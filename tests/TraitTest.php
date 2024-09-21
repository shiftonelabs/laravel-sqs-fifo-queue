<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests;

use ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes\Job;

class TraitTest extends TestCase
{
    public function test_trait_can_set_message_group_id()
    {
        $job = new Job();

        $job->onMessageGroup('test');

        $this->assertEquals('test', $job->messageGroupId);
    }

    public function test_trait_can_set_deduplicator()
    {
        $job = new Job();

        $job->withDeduplicator('test');

        $this->assertEquals('test', $job->deduplicator);
    }

    public function test_trait_can_unset_deduplicator()
    {
        $job = new Job();

        $job->withDeduplicator('test');

        $this->assertEquals('test', $job->deduplicator);

        $job->withoutDeduplicator();

        $this->assertEquals('', $job->deduplicator);
    }
}
