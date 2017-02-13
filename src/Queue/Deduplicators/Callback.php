<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Queue\Deduplicators;

use Closure;
use ShiftOneLabs\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;

class Callback implements Deduplicator
{
    /**
     * The user defined callback function to generate the deduplication id.
     *
     * @var \Closure
     */
    protected $callback;

    /**
     * Create a new deduplicator instance.
     *
     * @param  \Closure  $callback
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
     *
     * @param  string  $payload
     * @param  string  $queue
     *
     * @return string
     */
    public function generate($payload, $queue)
    {
        return call_user_func($this->callback, $payload, $queue);
    }
}
