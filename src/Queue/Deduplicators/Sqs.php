<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Queue\Deduplicators;

use ShiftOneLabs\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;

class Sqs implements Deduplicator
{
    /**
     * Do not generate a deduplication id.
     *
     * This deduplicator should be used for queues where Amazon's
     * ContentBasedDeduplication features is enabled on SQS.
     *
     * @param  string  $payload
     * @param  string  $queue
     *
     * @return bool
     */
    public function generate($payload, $queue)
    {
        return false;
    }
}
