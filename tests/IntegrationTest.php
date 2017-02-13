<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests;

use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\Job;
use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\StandardJob;

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
}
