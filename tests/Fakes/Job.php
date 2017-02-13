<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests\Fakes;

use ShiftOneLabs\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;

class Job
{
    use SqsFifoQueueable;
}
