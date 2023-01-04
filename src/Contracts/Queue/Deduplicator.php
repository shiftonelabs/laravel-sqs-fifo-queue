<?php

namespace Bisnow\LaravelSqsFifoQueue\Contracts\Queue;

interface Deduplicator
{
    /**
     * Generate a deduplication id to determine if a message is a duplicate.
     *
     * @param  string  $payload
     * @param  string  $queue
     *
     * @return string|bool
     */
    public function generate($payload, $queue);
}
