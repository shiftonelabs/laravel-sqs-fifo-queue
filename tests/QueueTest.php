<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests;

use Aws\Result;
use Mockery as m;
use Aws\Sqs\SqsClient;
use BadMethodCallException;
use InvalidArgumentException;
use ShiftOneLabs\LaravelSqsFifoQueue\SqsFifoQueue;
use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\Job;
use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\StandardJob;
use ShiftOneLabs\LaravelSqsFifoQueue\Queue\Connectors\SqsFifoConnector;

class QueueTest extends TestCase
{
    public function test_queue_sends_message_group_id()
    {
        $group = 'default';
        $job = 'test';
        $closure = function ($message) use ($group) {
            if ($message['MessageGroupId'] != $group) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', $group, '');
        $queue->setContainer($this->app);

        $queue->pushRaw($job);
    }

    public function test_queue_sends_message_group_id_from_job()
    {
        $group = 'job-group';
        $job = new Job();
        $job->onMessageGroup($group);
        $closure = function ($message) use ($group) {
            if ($message['MessageGroupId'] != $group) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', '');
        $queue->setContainer($this->app);

        $queue->push($job);
    }

    public function test_queue_uses_deduplicator_from_job()
    {
        $deduplication = 'content';
        $job = new Job();
        $job->withDeduplicator($deduplication);
        $closure = function ($message) use ($deduplication) {
            $deduplicator = $this->app->make('queue.sqs-fifo.deduplicator.'.$deduplication);
            $deduplicationId = $deduplicator->generate($message['MessageBody'], null);
            if (!array_key_exists('MessageDeduplicationId', $message) || $deduplicationId != $message['MessageDeduplicationId']) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', '');
        $queue->setContainer($this->app);

        $queue->push($job);
    }

    public function test_queue_ignores_unset_deduplicator_from_job()
    {
        $job = new Job();
        $closure = function ($message) {
            if (!array_key_exists('MessageDeduplicationId', $message)) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', 'unique');
        $queue->setContainer($this->app);

        $queue->push($job);
    }

    public function test_queue_uses_blank_deduplicator_from_job()
    {
        $job = new Job();
        $job->withoutDeduplicator();
        $closure = function ($message) {
            if (array_key_exists('MessageDeduplicationId', $message)) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', 'unique');
        $queue->setContainer($this->app);

        $queue->push($job);
    }

    public function test_queue_sends_unique_message_deduplication_id()
    {
        $job = 'test';
        $deduplication = 'unique';
        $closure = function ($message) {
            if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $message['MessageDeduplicationId'])) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', $deduplication);
        $queue->setContainer($this->app);

        $queue->pushRaw($job);
    }

    public function test_queue_sends_content_message_deduplication_id()
    {
        $job = 'test';
        $deduplication = 'content';
        $closure = function ($message) use ($job) {
            if (hash('sha256', $job) != $message['MessageDeduplicationId']) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', $deduplication);
        $queue->setContainer($this->app);

        $queue->pushRaw($job);
    }

    public function test_queue_doesnt_send_sqs_message_deduplication_id_with_sqs_deduplicator()
    {
        $job = 'test';
        $deduplication = 'sqs';
        $closure = function ($message) {
            if (array_key_exists('MessageDeduplicationId', $message)) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', $deduplication);
        $queue->setContainer($this->app);

        $queue->pushRaw($job);
    }

    public function test_queue_doesnt_send_sqs_message_deduplication_id_with_blank_deduplicator()
    {
        $job = 'test';
        $deduplication = '';
        $closure = function ($message) {
            if (array_key_exists('MessageDeduplicationId', $message)) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', $deduplication);
        $queue->setContainer($this->app);

        $queue->pushRaw($job);
    }

    public function test_queue_sends_custom_message_deduplication_id()
    {
        $this->bind_custom_deduplicator();

        $job = 'test';
        $deduplication = 'custom';
        $closure = function ($message) use ($job) {
            if ('custom' != $message['MessageDeduplicationId']) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', $deduplication);
        $queue->setContainer($this->app);

        $queue->pushRaw($job);
    }

    public function test_queue_throws_exception_with_invalid_deduplicator()
    {
        $this->bind_invalid_custom_deduplicator();

        $job = 'test';
        $deduplication = 'custom';

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', $deduplication);
        $queue->setContainer($this->app);

        $this->setExpectedException(InvalidArgumentException::class, 'Deduplication method ['.$deduplication.'] must resolve to a');

        $queue->pushRaw($job);
    }

    public function test_queue_throws_exception_with_unbound_deduplicator()
    {
        $job = 'test';
        $deduplication = 'custom';

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', $deduplication);
        $queue->setContainer($this->app);

        $this->setExpectedException(InvalidArgumentException::class, 'Unsupported deduplication method ['.$deduplication.'].');

        $queue->pushRaw($job);
    }

    public function test_later_throws_exception()
    {
        $job = 'test';
        $client = m::mock(SqsClient::class);
        $queue = new SqsFifoQueue($client, '');

        $this->setExpectedException(BadMethodCallException::class);

        $queue->later(10, $job);
    }

    public function test_set_sqs_sets_sqs()
    {
        $client1 = m::mock(SqsClient::class);
        $queue = new SqsFifoQueue($client1, '');

        $client2 = m::mock(SqsClient::class);
        $queue->setSqs($client2);

        $this->assertNotSame($queue->getSqs(), $client1);
        $this->assertSame($queue->getSqs(), $client2);
    }

    public function test_push_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';
        $config = $this->app['config']["queue.connections.{$connection}"];

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->andReturn($result);

        $queue = $this->queue->connection($connection);
        $queue->setSqs($client);

        $id = $queue->push(Job::class, ['with' => 'data']);

        $this->assertEquals('1234', $id);
    }

    public function test_push_standard_job_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';
        $config = $this->app['config']["queue.connections.{$connection}"];

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->andReturn($result);

        $queue = $this->queue->connection($connection);
        $queue->setSqs($client);

        $id = $queue->push(StandardJob::class, ['with' => 'data']);

        $this->assertEquals('1234', $id);
    }

    protected function bind_custom_deduplicator()
    {
        $this->app->bind('queue.sqs-fifo.deduplicator.custom', function () {
            return new \ShiftOneLabs\LaravelSqsFifoQueue\Queue\Deduplicators\Callback(function ($payload, $queue) {
                return 'custom';
            });
        });
    }

    protected function bind_invalid_custom_deduplicator()
    {
        $this->app->bind('queue.sqs-fifo.deduplicator.custom', function () {
            return 'custom';
        });
    }
}
