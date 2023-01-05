<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Tests\Fakes;

use Bisnow\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;

class Notification extends BaseNotification implements ShouldQueue
{
    use Queueable, SqsFifoQueueable;

    //
}
