<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators;

use Bisnow\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;

class Content implements Deduplicator
{
    /**
     * Generate a deduplication id based on the hash of the message.
     *
     * This deduplicator should be used for queues that should treat
     * identical payloads as duplicate messages.
     */
    public function generate(string $payload, ?string $queue): string
    {
        return hash('sha256', $payload);
    }
}
