<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Tests;

use Bisnow\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider;
use Dotenv\Dotenv;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use ReflectionMethod;
use ReflectionProperty;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use InteractsWithContainer;

    /**
     * Initial setup for all tests.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->loadEnvironment();

        $this->setUpQueueConnection();
    }

    /**
     * Load the environment variables from the .env file.
     *
     * @return void
     */
    public function loadEnvironment()
    {
        app()->loadEnvironmentFrom('.env');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelSqsFifoQueueServiceProvider::class,
        ];
    }

    /**
     * Setup the database connection.
     *
     * @return void
     */
    public function setUpQueueConnection()
    {
        $queueName = env('SQS_QUEUE') ?: 'queuename.fifo';

        config()->set('queue.connections.sqs-fifo', [
            'driver' => 'sqs-fifo',
            'key' => env('SQS_KEY'),
            'secret' => env('SQS_SECRET'),
            'prefix' => env('SQS_PREFIX', ''),
            'queue' => $queueName,
            'region' => env('SQS_REGION', 'us-east-1'),
            'group' => 'default',
            'deduplicator' => 'unique',
            'allow_delay' => false,
        ]);

        config()->set('queue.connections.sqs-fifo-no-credentials', [
            'driver' => 'sqs-fifo',
            'prefix' => env('SQS_PREFIX', '_test'),
            'queue' => $queueName,
            'region' => env('SQS_REGION', 'us-east-1'),
            'group' => 'default',
            'deduplicator' => 'unique',
            'allow_delay' => false,
        ]);
    }

    /**
     * Use reflection to call a restricted (private/protected) method on an object.
     *
     * @param  object  $object
     * @param  string  $method
     * @param  array  $args
     *
     * @return mixed
     */
    protected function callRestrictedMethod($object, $method, array $args = [])
    {
        $reflectionMethod = new ReflectionMethod($object, $method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $args);
    }

    /**
     * Use reflection to get the value of a restricted (private/protected)
     * property on an object.
     *
     * @param  object  $object
     * @param  string  $property
     *
     * @return mixed
     */
    protected function getRestrictedValue($object, $property)
    {
        $reflectionProperty = new ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
