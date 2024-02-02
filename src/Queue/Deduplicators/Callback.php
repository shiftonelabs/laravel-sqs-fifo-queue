<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Queue\Deduplicators;

use Bisnow\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;
use Closure;

class Callback implements Deduplicator
{
    /**
     * The user defined callback function to generate the deduplication id.
     *
     * @var Closure
     */
    protected $callback;

    /**
     * Create a new deduplicator instance.
     *
     * @param  Closure  $callback
     *
     * @return void
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Generate a deduplication id using the defined callback function.
     *
     * This deduplicator can be used to allow a developer to quickly generate
     * a custom deduplicator using a Closure, without having to implement
     * a completely new deduplicator object.
     */
    public function generate(string $payload, ?string $queue): string
    {
        return call_user_func($this->callback, $payload, $queue);
    }
}
