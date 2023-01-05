<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Tests;

use Bisnow\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;
use Bisnow\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider;
use Bisnow\LaravelSqsFifoQueue\Queue\Connectors\SqsFifoConnector;
use Illuminate\Container\Container;
use Illuminate\Queue\QueueServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_sqs_fifo_driver_is_registered()
    {
        $connector = new SqsFifoConnector();

        $this->assertInstanceOf(SqsFifoConnector::class, $connector);
    }

    public function test_unique_deduplicator_is_registered()
    {
        $deduplicator = $this->app->make('queue.sqs-fifo.deduplicator.unique');

        $this->assertInstanceOf(Deduplicator::class, $deduplicator);
    }

    public function test_content_deduplicator_is_registered()
    {
        $deduplicator = $this->app->make('queue.sqs-fifo.deduplicator.content');

        $this->assertInstanceOf(Deduplicator::class, $deduplicator);
    }

    public function test_sqs_deduplicator_is_registered()
    {
        $deduplicator = $this->app->make('queue.sqs-fifo.deduplicator.sqs');

        $this->assertInstanceOf(Deduplicator::class, $deduplicator);
    }

    public function test_sqs_fifo_driver_is_registered_with_laravel_container()
    {
        $container = $this->setup_laravel_container();

        $connector = $this->callRestrictedMethod($container['queue'], 'getConnector', ['sqs-fifo']);

        $this->assertInstanceOf(SqsFifoConnector::class, $connector);
    }

    public function test_unique_deduplicator_is_registered_with_laravel_container()
    {
        $container = $this->setup_laravel_container();

        $deduplicator = $container->make('queue.sqs-fifo.deduplicator.unique');

        $this->assertInstanceOf(Deduplicator::class, $deduplicator);
    }

    public function test_content_deduplicator_is_registered_with_laravel_container()
    {
        $container = $this->setup_laravel_container();

        $deduplicator = $container->make('queue.sqs-fifo.deduplicator.content');

        $this->assertInstanceOf(Deduplicator::class, $deduplicator);
    }

    public function test_sqs_deduplicator_is_registered_with_laravel_container()
    {
        $container = $this->setup_laravel_container();

        $deduplicator = $container->make('queue.sqs-fifo.deduplicator.sqs');

        $this->assertInstanceOf(Deduplicator::class, $deduplicator);
    }

    public function test_reversed_registration_still_works()
    {
        $container = new Container();

        // Only register the queue manager to avoid events dependency.
        (new LaravelSqsFifoQueueServiceProvider($container))->register();
        $this->callRestrictedMethod(new QueueServiceProvider($container), 'registerManager');

        $connector = $this->callRestrictedMethod($container['queue'], 'getConnector', ['sqs-fifo']);

        $this->assertInstanceOf(SqsFifoConnector::class, $connector);
    }

    protected function setup_laravel_container()
    {
        $container = new Container();

        // Only register the queue manager to avoid events dependency.
        $this->callRestrictedMethod(new QueueServiceProvider($container), 'registerManager');
        (new LaravelSqsFifoQueueServiceProvider($container))->register();

        return $container;
    }
}
