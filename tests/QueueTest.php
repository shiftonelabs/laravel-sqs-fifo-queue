<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests;

use Aws\Result;
use Mockery as m;
use Aws\Sqs\SqsClient;
use BadMethodCallException;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Queue\CallQueuedHandler;
use ShiftOneLabs\LaravelSqsFifoQueue\SqsFifoQueue;
use Illuminate\Notifications\SendQueuedNotifications;
use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\Job;
use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\Mail;
use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\StandardJob;
use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\Notification;

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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, $group, '');
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, 'queue-group', '');
        $queue->setContainer($this->app);

        $queue->push($job);
    }

    public function test_queue_sends_message_group_id_from_notification()
    {
        if (!class_exists(SendQueuedNotifications::class)) {
            return $this->markTestSkipped('This version does not support notifications.');
        }

        $group = 'job-group';
        $notification = new Notification();
        $notification->onMessageGroup($group);
        $job = new SendQueuedNotifications(new Collection(['notifiables']), $notification);
        $closure = function ($message) use ($group) {
            if ($message['MessageGroupId'] != $group) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, 'queue-group', '');
        $queue->setContainer($this->app);

        $queue->push($job);
    }

    public function test_queue_sends_message_group_id_from_mailable()
    {
        $group = 'job-group';
        $mailable = new Mail();
        $mailable->onMessageGroup($group);
        $job = new SendQueuedMailable($mailable);
        $closure = function ($message) use ($group) {
            if ($message['MessageGroupId'] != $group) {
                return false;
            }

            return true;
        };

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, 'queue-group', '');
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', '');
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', 'unique');
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', 'unique');
        $queue->setContainer($this->app);

        $queue->push($job);
    }

    public function test_queue_uses_deduplicator_from_notification()
    {
        $deduplication = 'content';
        $notification = new Notification();
        $notification->withDeduplicator($deduplication);
        $job = new SendQueuedNotifications(new Collection(['notifiables']), $notification);
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', '');
        $queue->setContainer($this->app);

        $queue->push($job);
    }

    public function test_queue_uses_deduplicator_from_mailable()
    {
        $deduplication = 'content';
        $mailable = new Mail();
        $mailable->withDeduplicator($deduplication);
        $job = new SendQueuedMailable($mailable);
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', '');
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', $deduplication);
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', $deduplication);
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', $deduplication);
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', $deduplication);
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
        $client->shouldReceive('sendMessage')->once()->with(m::on($closure))->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', $deduplication);
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
        $client->shouldReceive('sendMessage')->never()->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', $deduplication);
        $queue->setContainer($this->app);

        $this->expectException(InvalidArgumentException::class, 'Deduplication method ['.$deduplication.'] must resolve to a');
        $this->expectExceptionMessage('Deduplication method ['.$deduplication.'] must resolve to a');

        $queue->pushRaw($job);
    }

    public function test_queue_throws_exception_with_unbound_deduplicator()
    {
        $job = 'test';
        $deduplication = 'custom';

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->never()->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', $deduplication);
        $queue->setContainer($this->app);

        $this->expectException(InvalidArgumentException::class, 'Unsupported deduplication method ['.$deduplication.'].');
        $this->expectExceptionMessage('Unsupported deduplication method ['.$deduplication.'].');

        $queue->pushRaw($job);
    }

    public function test_later_throws_exception_without_allow_delay()
    {
        $job = 'test';
        $client = m::mock(SqsClient::class);
        $queue = new SqsFifoQueue($client, '');

        $this->expectException(BadMethodCallException::class);

        $queue->later(10, $job);
    }

    public function test_later_pushes_job_with_allow_delay()
    {
        $job = 'test';

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->once()->andReturn($result);

        $queue = new SqsFifoQueue($client, '', '', '', false, '', '', true);
        $queue->setContainer($this->app);
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

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->once()->andReturn($result);

        $queue = $this->queue->connection($connection);
        $queue->setSqs($client);

        $id = $queue->push(Job::class, ['with' => 'data']);

        $this->assertEquals('1234', $id);
    }

    public function test_push_standard_job_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';

        $result = new Result(['MessageId' => '1234']);
        $client = m::mock(SqsClient::class);
        $client->shouldReceive('sendMessage')->once()->andReturn($result);

        $queue = $this->queue->connection($connection);
        $queue->setSqs($client);

        $id = $queue->push(StandardJob::class, ['with' => 'data']);

        $this->assertEquals('1234', $id);
    }

    public function test_get_queue_properly_resolves_url_with_prefix_support()
    {
        $client = m::mock(SqsClient::class);
        $prefix = 'https://sqs.us-east-1.amazonaws.com/123456789012';
        $suffix = '-staging';
        $queueName = 'queue';
        $queueFifoName = $queueName.'.fifo';
        $queueFifoNameWithSuffix = $queueName.$suffix.'.fifo';
        $queueUrl = $prefix.'/'.$queueFifoName;

        // Make sure the queue is built without a prefix or suffix.
        $queue = new SqsFifoQueue($client, $queueFifoName);
        $this->assertEquals('/'.$queueFifoName, $queue->getQueue(null));
        $this->assertEquals('/'.$queueFifoName, $queue->getQueue($queueFifoName));

        // Make sure the queue is built with a prefix and not a suffix.
        $queue = new SqsFifoQueue($client, $queueFifoName, $prefix);
        $this->assertEquals($prefix.'/'.$queueFifoName, $queue->getQueue(null));
        $this->assertEquals($prefix.'/'.$queueFifoName, $queue->getQueue($queueFifoName));

        // Make sure the queue is built with a suffix and not a prefix.
        $queue = new SqsFifoQueue($client, $queueFifoName, '', $suffix);
        $this->assertEquals('/'.$queueFifoNameWithSuffix, $queue->getQueue(null));
        $this->assertEquals('/'.$queueFifoNameWithSuffix, $queue->getQueue($queueFifoName));

        // Make sure the queue is built with both a prefix and a suffix.
        $queue = new SqsFifoQueue($client, $queueFifoName, $prefix, $suffix);
        $this->assertEquals($prefix.'/'.$queueFifoNameWithSuffix, $queue->getQueue(null));
        $this->assertEquals($prefix.'/'.$queueFifoNameWithSuffix, $queue->getQueue($queueFifoName));

        // Make sure the queue name is only suffixed once.
        $queue = new SqsFifoQueue($client, $queueFifoNameWithSuffix, $prefix, $suffix);
        $this->assertEquals($prefix.'/'.$queueFifoNameWithSuffix, $queue->getQueue(null));
        $this->assertEquals($prefix.'/'.$queueFifoNameWithSuffix, $queue->getQueue($queueFifoNameWithSuffix));

        // Make sure the queue name isn't modified if it's already a full url.
        $queue = new SqsFifoQueue($client, $queueUrl, $prefix, $suffix);
        $this->assertEquals($queueUrl, $queue->getQueue(null));
        $this->assertEquals($queueUrl, $queue->getQueue($queueUrl));
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
