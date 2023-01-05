<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Tests;

use Bisnow\LaravelSqsFifoQueue\Tests\Fakes\Job;

class TraitTest extends TestCase
{
    public function test_trait_can_set_message_group_id()
    {
        $job = new Job();

        $job->onMessageGroup('test');

        $this->assertEquals('test', $job->messageGroupId);
    }
}
