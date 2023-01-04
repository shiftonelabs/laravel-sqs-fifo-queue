<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators;

use Bisnow\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;

class Sqs implements Deduplicator
{
    /**
     * Do not generate a deduplication id.
     *
     * This deduplicator should be used for queues where Amazon's
     * ContentBasedDeduplication features is enabled on SQS.
     */
    public function generate(string $payload, ?string $queue): bool
    {
        return false;
    }
}
