<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators;

use Bisnow\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;
use Ramsey\Uuid\Uuid;

class Unique implements Deduplicator
{
    /**
     * Generate a unique deduplication id.
     *
     * This deduplicator should be used for queues that should treat all messages
     * as unique, even if the payload is identical to another message.
     */
    public function generate(string $payload, ?string $queue): string
    {
        return Uuid::uuid4()->toString();
    }
}
