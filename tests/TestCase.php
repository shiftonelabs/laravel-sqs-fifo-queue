<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests;

use Exception;
use Dotenv\Dotenv;
use ReflectionMethod;
use ReflectionProperty;
use Illuminate\Queue\Queue;
use Illuminate\Queue\SqsQueue;
use Illuminate\Encryption\Encrypter;
use Illuminate\Queue\Capsule\Manager as Capsule;
use PHPUnit\Framework\TestCase as PhpunitTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ShiftOneLabs\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider;

class TestCase extends PhpunitTestCase
{
    /**
     * Use the integration trait so PHPUnit understands Mockery assertions.
     */
    use MockeryPHPUnitIntegration;

    /**
     * The Illuminate Container used by the queue.
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * The Queue Capsule instance for the tests.
     *
     * @var \Illuminate\Queue\Capsule\Manager
     */
    protected $queue;

    /**
     * Initial setup for all tests.
     *
     * @return void
     *
     * @before
     */
    public function beforeSetup()
    {
        $this->loadEnvironment();
        $this->setUpCapsule();
        $this->setUpQueueConnection();
        $this->registerServiceProvider();
    }

    /**
     * Load the environment variables from the .env file.
     *
     * @return void
     */
    public function loadEnvironment()
    {
        (Dotenv::createUnsafeImmutable(__DIR__.'/..'))->safeLoad();
    }

    /**
     * Setup the Queue Capsule.
     *
     * @return void
     */
    public function setUpCapsule()
    {
        $queue = new Capsule();
        $queue->setAsGlobal();

        $this->queue = $queue;
        $this->app = $queue->getContainer();

        $this->app->instance('queue', $queue->getQueueManager());
    }

    /**
     * Register the service provider for the package.
     *
     * @return void
     */
    public function registerServiceProvider()
    {
        $provider = new LaravelSqsFifoQueueServiceProvider($this->app);

        $provider->register();
    }

    /**
     * Setup the database connection.
     *
     * @return void
     */
    public function setUpQueueConnection()
    {
        $queue = $this->queue;

        $queue->addConnection([
            'driver' => 'sync',
        ]);

        $queue->addConnection([
            'driver' => 'sqs-fifo',
            'key' => getenv('AWS_ACCESS_KEY_ID'),
            'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            'prefix' => getenv('SQS_FIFO_PREFIX'),
            'queue' => getenv('SQS_FIFO_QUEUE') ?: 'queuename.fifo',
            'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
            'after_commit' => false,
            'group' => 'default',
            'deduplicator' => getenv('SQS_FIFO_DEDUPLICATOR') ?: 'unique',
            'allow_delay' => getenv('SQS_FIFO_ALLOW_DELAY') ?: false,
        ], 'sqs-fifo');

        $queue->addConnection([
            'driver' => 'sqs-fifo',
            'prefix' => getenv('SQS_FIFO_PREFIX'),
            'queue' => getenv('SQS_FIFO_QUEUE') ?: 'queuename.fifo',
            'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
            'after_commit' => false,
            'group' => 'default',
            'deduplicator' => getenv('SQS_FIFO_DEDUPLICATOR') ?: 'unique',
            'allow_delay' => getenv('SQS_FIFO_ALLOW_DELAY') ?: false,
        ], 'sqs-fifo-no-credentials');
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

    /**
     * Use reflection to set the value of a restricted (private/protected)
     * property on an object.
     *
     * @param  object  $object
     * @param  string  $property
     * @param  mixed  $value
     *
     * @return void
     */
    protected function setRestrictedValue($object, $property, $value)
    {
        $reflectionProperty = new ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        if ($reflectionProperty->isStatic()) {
            $reflectionProperty->setValue($value);
        } else {
            $reflectionProperty->setValue($object, $value);
        }
    }
}
