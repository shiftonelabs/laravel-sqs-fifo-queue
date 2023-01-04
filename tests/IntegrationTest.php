<?php

namespace Bisnow\LaravelSqsFifoQueue\Tests;

use Bisnow\LaravelSqsFifoQueue\Tests\Fakes\Job;
use Bisnow\LaravelSqsFifoQueue\Tests\Fakes\StandardJob;

class IntegrationTest extends TestCase
{
    public function test_push_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';
        $config = $this->app['config']["queue.connections.{$connection}"];

        if (empty($config['key']) || empty($config['secret']) || empty($config['prefix']) || empty($config['queue']) || empty($config['region'])) {
            return $this->markTestSkipped('SQS config missing key, secret, prefix, queue, or region');
        }

        $id = $this->queue->connection($connection)->push(Job::class, ['with' => 'data']);

        $this->assertNotNull($id);
    }

    public function test_push_standard_job_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';
        $config = $this->app['config']["queue.connections.{$connection}"];

        if (empty($config['key']) || empty($config['secret']) || empty($config['prefix']) || empty($config['queue']) || empty($config['region'])) {
            return $this->markTestSkipped('SQS config missing key, secret, prefix, queue, or region');
        }

        $id = $this->queue->connection($connection)->push(StandardJob::class, ['with' => 'data']);

        $this->assertNotNull($id);
    }

    public function test_push_job_instance_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';
        $config = $this->app['config']["queue.connections.{$connection}"];

        if (empty($config['key']) || empty($config['secret']) || empty($config['prefix']) || empty($config['queue']) || empty($config['region'])) {
            return $this->markTestSkipped('SQS config missing key, secret, prefix, queue, or region');
        }

        $id = $this->queue->connection($connection)->push((new Job)->onMessageGroup('instance-test'), ['with' => 'data']);

        $this->assertNotNull($id);
    }

    public function test_push_to_fifo_queue_works_with_alternate_credentials()
    {
        $connection = 'sqs-fifo-no-credentials';
        $config = $this->app['config']["queue.connections.{$connection}"];

        if (empty($config['prefix']) || empty($config['queue']) || empty($config['region'])) {
            return $this->markTestSkipped('SQS config missing prefix, queue, or region');
        }

        if (empty(getenv('AWS_ACCESS_KEY_ID')) || empty(getenv('AWS_SECRET_ACCESS_KEY'))) {
            return $this->markTestSkipped('Environment missing alternate SQS credentials');
        }

        $id = $this->queue->connection($connection)->push(Job::class, ['with' => 'data']);

        $this->assertNotNull($id);
    }
}
