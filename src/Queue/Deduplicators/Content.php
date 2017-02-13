<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Queue\Deduplicators;

use ShiftOneLabs\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;

class Content implements Deduplicator
{
    /**
     * Generate a deduplication id based on the hash of the message.
     *
     * This deduplicator should be used for queues that should treat
     * identical payloads as duplicate messages.
     *
     * @param  string  $payload
     * @param  string  $queue
     *
     * @return string
     */
    public function generate($payload, $queue)
    {
        return hash('sha256', $payload);
    }
}
