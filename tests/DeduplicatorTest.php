<?php

namespace Bisnow\LaravelSqsFifoQueue\Tests;

use Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators\Sqs;
use Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators\Unique;
use Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators\Content;
use Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators\Callback;

class DeduplicatorTest extends TestCase
{
    public function test_unique_deduplicator_returns_unique_id_for_same_content()
    {
        $deduplicator = new Unique();

        $id1 = $deduplicator->generate('content', null);
        $id2 = $deduplicator->generate('content', null);

        $this->assertNotEquals($id1, $id2);
    }

    public function test_unique_deduplicator_returns_unique_id_for_different_content()
    {
        $deduplicator = new Unique();

        $id1 = $deduplicator->generate('content one', null);
        $id2 = $deduplicator->generate('content two', null);

        $this->assertNotEquals($id1, $id2);
    }

    public function test_content_deduplicator_returns_same_id_for_same_content()
    {
        $deduplicator = new Content();

        $id1 = $deduplicator->generate('content', null);
        $id2 = $deduplicator->generate('content', null);

        $this->assertEquals($id1, $id2);
    }

    public function test_content_deduplicator_returns_different_id_for_different_content()
    {
        $deduplicator = new Content();

        $id1 = $deduplicator->generate('content one', null);
        $id2 = $deduplicator->generate('content two', null);

        $this->assertNotEquals($id1, $id2);
    }

    public function test_sqs_deduplicator_returns_false()
    {
        $deduplicator = new Sqs();

        $id = $deduplicator->generate('content', null);

        $this->assertFalse($id);
    }

    public function test_callback_deduplicator_returns_callback_result()
    {
        $deduplicator = new Callback(function ($payload, $queue) {
            return 'result';
        });

        $id = $deduplicator->generate('content', null);

        $this->assertEquals('result', $id);
    }
}
