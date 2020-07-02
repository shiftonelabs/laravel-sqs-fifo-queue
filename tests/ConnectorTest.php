<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests;

use InvalidArgumentException;
use ShiftOneLabs\LaravelSqsFifoQueue\Support\Arr;
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

    public function test_sqs_fifo_driver_creates_queue_with_missing_prefix()
    {
        $config = $this->getConfig([], ['prefix']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);

        if (property_exists($queue, 'prefix')) {
            $this->assertEquals('', $this->getRestrictedValue($queue, 'prefix'));
        }
    }

    public function test_sqs_fifo_driver_creates_queue_with_empty_prefix()
    {
        $config = $this->getConfig(['prefix' => '']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);

        if (property_exists($queue, 'prefix')) {
            $this->assertEquals('', $this->getRestrictedValue($queue, 'prefix'));
        }
    }

    public function test_sqs_fifo_driver_creates_queue_with_valid_prefix()
    {
        $config = $this->getConfig();
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);

        if (property_exists($queue, 'prefix')) {
            $this->assertNotEmpty($config['prefix']);
            $this->assertEquals($config['prefix'], $this->getRestrictedValue($queue, 'prefix'));
        }
    }

    public function test_sqs_fifo_driver_creates_queue_with_missing_suffix()
    {
        $config = $this->getConfig([], ['suffix']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertEquals('', $this->getRestrictedValue($queue, 'suffix'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_empty_suffix()
    {
        $config = $this->getConfig(['suffix' => '']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertEquals('', $this->getRestrictedValue($queue, 'suffix'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_valid_suffix()
    {
        $config = $this->getConfig();
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertNotEmpty($config['suffix']);
        $this->assertEquals($config['suffix'], $this->getRestrictedValue($queue, 'suffix'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_default_group_when_missing_group()
    {
        $config = $this->getConfig([], ['group']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertEquals('default', $this->getRestrictedValue($queue, 'group'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_empty_group()
    {
        $config = $this->getConfig(['group' => '']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertEquals('', $this->getRestrictedValue($queue, 'group'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_valid_group()
    {
        $config = $this->getConfig();
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertNotEmpty($config['group']);
        $this->assertEquals($config['group'], $this->getRestrictedValue($queue, 'group'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_default_deduplicator_when_missing_deduplicator()
    {
        $config = $this->getConfig([], ['deduplicator']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertEquals('unique', $this->getRestrictedValue($queue, 'deduplicator'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_empty_deduplicator()
    {
        $config = $this->getConfig(['deduplicator' => '']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertEquals('', $this->getRestrictedValue($queue, 'deduplicator'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_valid_deduplicator()
    {
        $config = $this->getConfig();
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertNotEmpty($config['deduplicator']);
        $this->assertEquals($config['deduplicator'], $this->getRestrictedValue($queue, 'deduplicator'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_default_allow_delay_when_missing_allow_delay()
    {
        $config = $this->getConfig([], ['allow_delay']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertEquals(false, $this->getRestrictedValue($queue, 'allowDelay'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_empty_allow_delay()
    {
        $config = $this->getConfig(['allow_delay' => '']);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertEquals(false, $this->getRestrictedValue($queue, 'allowDelay'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_valid_allow_delay_as_false()
    {
        $config = $this->getConfig(['allow_delay' => false]);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertFalse($config['allow_delay']);
        $this->assertEquals($config['allow_delay'], $this->getRestrictedValue($queue, 'allowDelay'));
    }

    public function test_sqs_fifo_driver_creates_queue_with_valid_allow_delay_as_true()
    {
        $config = $this->getConfig(['allow_delay' => true]);
        $connector = new SqsFifoConnector();

        $queue = $connector->connect($config);

        $this->assertInstanceOf(SqsFifoQueue::class, $queue);
        $this->assertTrue($config['allow_delay']);
        $this->assertEquals($config['allow_delay'], $this->getRestrictedValue($queue, 'allowDelay'));
    }

    protected function getConfig($overrides = [], $except = [])
    {
        return Arr::except(array_merge([
            'driver' => 'sqs-fifo',
            'key' => 'ABCDEFGHIJKLMNOPQRST',
            'secret' => '1a23bc/deFgHijKl4mNOp5qrS6TUVwXyz7ABCDef',
            'prefix' => 'https://sqs.us-east-1.amazonaws.com/123456789012',
            'suffix' => '-staging',
            'queue' => 'queuename.fifo',
            'region' => 'us-east-1',
            'group' => 'default',
            'deduplicator' => 'unique',
            'allow_delay' => false,
        ], $overrides), $except);
    }
}
