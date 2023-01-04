<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Tests;

use Bisnow\LaravelSqsFifoQueue\Tests\Fakes\Job;
use Bisnow\LaravelSqsFifoQueue\Tests\Fakes\StandardJob;

/**
 * A AWS SQS live connection is needed for these tests.
 */
class IntegrationTest extends TestCase
{
    public function test_push_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';
        $config = $this->app['config']["queue.connections.{$connection}"];

        if (empty($config['key']) || empty($config['secret']) || empty($config['prefix']) || empty($config['queue']) || empty($config['region'])) {
            return $this->markTestSkipped('SQS config missing key, secret, prefix, queue, or region');
        }

        $id = $this->app->queue->connection($connection)->push(Job::class, ['with' => 'data']);

        $this->assertNotNull($id);
    }

    public function test_push_standard_job_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';
        $config = $this->app['config']["queue.connections.{$connection}"];

        if (empty($config['key']) || empty($config['secret']) || empty($config['prefix']) || empty($config['queue']) || empty($config['region'])) {
            return $this->markTestSkipped('SQS config missing key, secret, prefix, queue, or region');
        }

        $id = $this->app->queue->connection($connection)->push(StandardJob::class, ['with' => 'data']);

        $this->assertNotNull($id);
    }

    public function test_push_job_instance_to_fifo_queue_returns_id()
    {
        $connection = 'sqs-fifo';
        $config = $this->app['config']["queue.connections.{$connection}"];

        if (empty($config['key']) || empty($config['secret']) || empty($config['prefix']) || empty($config['queue']) || empty($config['region'])) {
            return $this->markTestSkipped('SQS config missing key, secret, prefix, queue, or region');
        }

        $id = $this->app->queue->connection($connection)->push((new Job)->onMessageGroup('instance-test'), ['with' => 'data']);

        $this->assertNotNull($id);
    }

    public function test_push_to_fifo_queue_works_with_alternate_credentials()
    {
        $connection = 'sqs-fifo-no-credentials';
        $config = $this->app['config']["queue.connections.{$connection}"];

        if (empty($config['prefix']) || empty($config['queue']) || empty($config['region'])) {
            return $this->markTestSkipped('SQS config missing prefix, queue, or region');
        }

        if (empty(env('AWS_ACCESS_KEY_ID')) || empty(env('AWS_SECRET_ACCESS_KEY'))) {
            return $this->markTestSkipped('Environment missing alternate SQS credentials');
        }

        $id = $this->app->queue->connection($connection)->push(Job::class, ['with' => 'data']);

        $this->assertNotNull($id);
    }
}
