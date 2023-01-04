<?php

namespace Bisnow\LaravelSqsFifoQueue\Tests\Fakes;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Bisnow\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;

class Mail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SqsFifoQueueable;

    //
}
