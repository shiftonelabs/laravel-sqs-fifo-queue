<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests;

use InvalidArgumentException;
use ShiftOneLabs\LaravelSqsFifoQueue\SqsFifoQueue;
use ShiftOneLabs\LaravelSqsFifoQueue\Queue\Connectors\SqsFifoConnector;

class ConnectorTest extends TestCase
{
    public function test_sqs_fifo_driver_returns_sqs_fifo_queue()
    {
        $config = $this->app['config']['queue.connections.sqs-fifo'];
        $connector = new SqsFifoConnector();

        $connection = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $connection);
    }

    public function test_sqs_fifo_driver_throws_exception_with_invalid_queue_name()
    {
        $config = ['driver' => 'sqs-fifo', 'queue' => 'test'];
        $connector = new SqsFifoConnector();

        $this->setExpectedException(InvalidArgumentException::class);

        $connector->connect($config);
    }
}
