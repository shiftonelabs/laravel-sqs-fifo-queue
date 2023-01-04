<?php

namespace Bisnow\LaravelSqsFifoQueue\Tests\Fakes;

use Bisnow\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;

class Job
{
    use SqsFifoQueueable;
}
