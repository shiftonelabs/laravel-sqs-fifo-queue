<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Contracts\Queue;

interface Deduplicator
{
    /**
     * Generate a deduplication id to determine if a message is a duplicate.
     *
     * @param  string  $payload
     * @param  string|null  $queue
     *
     * @return string|bool
     */
    public function generate(string $payload, ?string $queue);
}
