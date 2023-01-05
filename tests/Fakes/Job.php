<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Tests\Fakes;

use Bisnow\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;

class Job
{
    use SqsFifoQueueable;
}
