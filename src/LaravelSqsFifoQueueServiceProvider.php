<?php

namespace Bisnow\LaravelSqsFifoQueue;

use Illuminate\Support\ServiceProvider;
use Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators\Sqs;
use Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators\Unique;
use Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators\Content;
use Bisnow\LaravelSqsFifoQueue\Queue\Connectors\SqsFifoConnector;

class LaravelSqsFifoQueueServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $this->registerDeduplicators();

        // Queue is a deferred provider. We don't want to force resolution to provide
        // a new driver. Therefore, if the queue has already been resolved, extend
        // it now. Otherwise, extend the queue after it has been resolved.
        if ($app->bound('queue')) {
            $this->extendManager($app['queue']);
        } else {
            // "afterResolving" not introduced until 5.0. Before 5.0 uses "resolving".
            if (method_exists($app, 'afterResolving')) {
                $app->afterResolving('queue', function ($manager) {
                    $this->extendManager($manager);
                });
            } else {
                $app->resolving('queue', function ($manager) {
                    $this->extendManager($manager);
                });
            }
        }
    }

    /**
     * Register everything for the given manager.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     *
     * @return void
     */
    public function extendManager($manager)
    {
        $this->registerConnectors($manager);
    }

    /**
     * Register the connectors on the queue manager.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     *
     * @return void
     */
    public function registerConnectors($manager)
    {
        $manager->extend('sqs-fifo', function () {
            return new SqsFifoConnector();
        });
    }

    /**
     * Register the default deduplicator methods.
     *
     * @return void
     */
    public function registerDeduplicators()
    {
        foreach (['Unique', 'Content', 'Sqs'] as $deduplicator) {
            $this->{"register{$deduplicator}Deduplicator"}();
        }
    }

    /**
     * Register the unique deduplicator to treat all messages as unique.
     *
     * @return void
     */
    public function registerUniqueDeduplicator()
    {
        $this->app->bind('queue.sqs-fifo.deduplicator.unique', Unique::class);
    }

    /**
     * Register the content deduplicator to treat messages with the same payload as duplicates.
     *
     * @return void
     */
    public function registerContentDeduplicator()
    {
        $this->app->bind('queue.sqs-fifo.deduplicator.content', Content::class);
    }

    /**
     * Register the SQS deduplicator for queues with ContentBasedDeduplication enabled on SQS.
     *
     * @return void
     */
    public function registerSqsDeduplicator()
    {
        $this->app->bind('queue.sqs-fifo.deduplicator.sqs', Sqs::class);
    }
}
