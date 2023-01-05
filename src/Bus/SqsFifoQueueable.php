<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Bus;

trait SqsFifoQueueable
{
    /**
     * The message group id the job should be sent to.
     */
    public string $messageGroupId = '';

    /**
     * The deduplication method to use for the job.
     */
    public ?string $deduplicator = null;

    /**
     * Set the desired message group id for the job.
     */
    public function onMessageGroup(string $messageGroupId): self
    {
        $this->messageGroupId = $messageGroupId;

        return $this;
    }

    /**
     * Set the desired deduplication method for the job.
     */
    public function withDeduplicator(string $deduplicator): self
    {
        $this->deduplicator = $deduplicator;

        return $this;
    }

    /**
     * Remove the deduplication method from the job.
     */
    public function withoutDeduplicator(): self
    {
        return $this->withDeduplicator('');
    }
}
