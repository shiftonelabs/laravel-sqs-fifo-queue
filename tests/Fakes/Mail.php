<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Tests\Fakes;

use Bisnow\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SqsFifoQueueable;

    //
}
