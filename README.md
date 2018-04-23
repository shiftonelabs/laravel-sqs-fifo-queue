# laravel-sqs-fifo-queue

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.txt)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This Laravel/Lumen package provides a queue driver for Amazon's SQS FIFO queues. While Laravel works with Amazon's SQS standard queues out of the box, FIFO queues are slightly different and are not handled properly by Laravel. That is where this package comes in.

## Install

Via Composer

``` bash
$ composer require shiftonelabs/laravel-sqs-fifo-queue
```

Once composer has been updated and the package has been installed, the service provider will need to be loaded.

For Laravel 4, open `app/config/app.php` and add following line to the providers array:

``` php
'ShiftOneLabs\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider',
```

For Laravel 5, open `config/app.php` and add following line to the providers array:
``` php
ShiftOneLabs\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider::class,
```

For Lumen 5, open `bootstrap/app.php` and add following line under the "Register Service Providers" section:
``` php
$app->register(ShiftOneLabs\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider::class);
```

## Configuration

#### Laravel/Lumen 5.1+ (5.1, 5.2, 5.3, 5.4)

If using Lumen, create a `config` directory in your project root if you don't already have one. Next, copy `vendor/laravel/lumen-framework/config/queue.php` to `config/queue.php`.

Now, for both Laravel and Lumen, open `config/queue.php` and add the following entry to the `connections` array.

    'sqs-fifo' => [
        'driver' => 'sqs-fifo',
        'key' => env('SQS_KEY'),
        'secret' => env('SQS_SECRET'),
        'prefix' => env('SQS_PREFIX'),
        'queue' => 'your-queue-name',    // ex: queuename.fifo
        'region' => 'your-queue-region', // ex: us-east-2
        'group' => 'default',
        'deduplicator' => 'unique',
    ],

Example .env file:

    SQS_KEY=ABCDEFGHIJKLMNOPQRST
    SQS_SECRET=1a23bc/deFgHijKl4mNOp5qrS6TUVwXyz7ABCDef
    SQS_PREFIX=https://sqs.us-east-2.amazonaws.com/123456789012

If you'd like this to be the default connection, also set `QUEUE_DRIVER=sqs-fifo` in the `.env` file.

#### Laravel/Lumen 5.0

If using Lumen, create a `config` directory in your project root if you don't already have one. Next, copy `vendor/laravel/lumen-framework/config/queue.php` to `config/queue.php`.

Now, for both Laravel and Lumen, open `config/queue.php` and add the following entry to the `connections` array:

    'sqs-fifo' => [
        'driver' => 'sqs-fifo',
        'key'    => env('SQS_KEY'),
        'secret' => env('SQS_SECRET'),
        'queue'  => env('SQS_PREFIX').'/your-queue-name',
        'region' => 'your-queue-region',
        'group' => 'default',
        'deduplicator' => 'unique',
    ],

Example .env file:

    SQS_KEY=ABCDEFGHIJKLMNOPQRST
    SQS_SECRET=1a23bc/deFgHijKl4mNOp5qrS6TUVwXyz7ABCDef
    SQS_PREFIX=https://sqs.us-east-2.amazonaws.com/123456789012

If you'd like this to be the default connection, also set `QUEUE_DRIVER=sqs-fifo` in the `.env` file.

#### Laravel 4

Open `app/config/queue.php` and add the following entry to the `connections` array:

    'sqs-fifo' => array(
        'driver' => 'sqs-fifo',
        'key'    => 'your-public-key',   // ex: ABCDEFGHIJKLMNOPQRST
        'secret' => 'your-secret-key',   // ex: 1a23bc/deFgHijKl4mNOp5qrS6TUVwXyz7ABCDef
        'queue'  => 'your-queue-url',    // ex: https://sqs.us-east-2.amazonaws.com/123456789012/queuename.fifo
        'region' => 'your-queue-region', // ex: us-east-2
        'group' => 'default',
        'deduplicator' => 'unique',
    ),

If you'd like this to be the default connection, also update the `'default'` key to `'sqs-fifo'`.

#### Capsule

If using the `illuminate\queue` component Capsule outside of Lumen/Laravel:

``` php
use Illuminate\Queue\Capsule\Manager as Queue;
use ShiftOneLabs\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider;

$queue = new Queue;

$queue->addConnection([
    'driver' => 'sqs-fifo',
    'key'    => 'your-public-key',   // ex: ABCDEFGHIJKLMNOPQRST
    'secret' => 'your-secret-key',   // ex: 1a23bc/deFgHijKl4mNOp5qrS6TUVwXyz7ABCDef
    /**
     * Set "prefix" and/or "queue" depending on version, as described for Laravel versions above
     * 'prefix' => 'your-prefix',
     * 'queue' => 'your-queue-name',
     */
    'region' => 'your-queue-region', // ex: us-east-2
    'group' => 'default',
    'deduplicator' => 'unique',
], 'sqs-fifo');

// Make this Capsule instance available globally via static methods... (optional)
$queue->setAsGlobal();

// Register the 'queue' alias in the Container, then register the SQS FIFO provider.
$app = $queue->getContainer();
$app->instance('queue', $queue->getQueueManager());
(new LaravelSqsFifoQueueServiceProvider($app))->register();
```

#### Credentials

The `key` and `secret` config options may be omitted if using one of the alternative options for providing AWS credentials (e.g. using an AWS credentials file). More informataion about this is available in the [AWS PHP SDK guide here](https://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html).

## Usage

For the most part, usage of this queue driver is the same as the built in queue drivers. There are, however, a few extra things to consider when working with Amazon's SQS FIFO queues.

#### Groups

In addition to being able to have multiple queue names for each connection, an SQS FIFO queue also allows one to have multiple "groups" for each FIFO queue. As an example, imagine a change sorter that had four queues setup, one each for quarters, dimes, nickels, and pennies. One could then setup groups inside the queues. For example, inside the penny queue, there could be a group for pre-1982 pennies and a group for post-1982 pennies.

By default, all queued jobs will be lumped into one group, as defined in the configuration file. In the configuration provided above, all queued jobs would be sent as part of the `default` group. The group can be changed per job using the `onMessageGroup()` method, which will be explained more below.

The group id must not be empty, must not be more than 128 characters, and can contain alphanumeric characters and punctuation (``!"#$%&'()*+,-./:;<=>?@[\]^_`{|}~``).

#### Deduplication

When sending jobs to the SQS FIFO queue, Amazon requires a way to determine if the job is a duplicate of a job already in the queue. SQS FIFO queues have a 5 minute deduplication interval, meaning that if a duplicate message is sent within the interval, it will be accepted successfully (no errors), but it will not be delivered or processed.

Determining duplicate messages is generally handled in one of two ways: either all messages are considered unique regardless of content, or messages are considered duplicates if they have the same content.

This package handles deduplication via the `deduplicator` configuration option.

To have all messages considered unique, set the `deduplicator` to `unique`.

To have messages with the same content considered duplicates, there are two options, depending on how the FIFO queue has been configured. If the FIFO queue has been setup in Amazon with the `Content-Based Deduplication` feature enabled, then the `deduplicator` should be set to `sqs`. This tells the connection to rely on Amazon to determine content uniqueness. However, if the `Content-Based Deduplication` feature is disabled, the `deduplicator` should be set to `content`. Note, if `Content-Based Deduplication` is disabled, and the `deduplicator` is set to `sqs`, this will generate an error when attempting to send a job to the queue.

To summarize:
- `sqs` - This is used when messages with the same content should be considered duplicates and `Content-Based Deduplication` is enabled on the SQS FIFO queue.
- `content` - This is used when messages with the same content should be considered duplicates but `Content-Based Deduplication` is disabled on the SQS FIFO queue.
- `unique` - This is used when all messages should be considered unique, regardless of content.

If there is a need for a different deduplication algorithm, custom deduplication methods can be registered in the container.

Finally, by default, all queued jobs will use the deduplicator defined in the configuration file. This can be changed per job using the `withDeduplicator()` method.

#### Delayed Jobs

SQS FIFO queues do not support per-message delays, only per-queue delays. The desired delay is defined on the queue itself when the queue is setup in the Amazon Console. Attempting to set a delay on a job sent to a FIFO queue will have no affect. To this end, using the `later()` method to push a job to an SQS FIFO queue will generate a `BadMethodCallException`.

To delay a job, you must `push()` the job to an SQS FIFO queue that has been defined with a delivery delay.

## Advanced Usage

#### Per-Job Group and Deduplicator

If you need to change the group or the deduplicator for a specific job, you will need access to the `onMessageGroup()` and `withDeduplicator()` methods. These methods are provided through the `ShiftOneLabs\LaravelSqsFifoQueue\Bus\SqsFifoQueueable` trait. Once you add this trait to your job class, you can change the group and/or the deduplicator for that specific job without affecting any other jobs on the queue.

#### Code Example

Job:

``` php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ShiftOneLabs\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;

class ProcessPenny implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SqsFifoQueueable, SerializesModels;

    //
}
```

Usage:

``` php
dispatch(
    (new \App\Jobs\ProcessPenny)
        ->onMessageGroup('post1982')
        ->withDeduplicator('unique')
);
```

#### Custom Deduplicator

The deduplicators work by generating a deduplication id that is sent to the queue. If two messages generate the same deduplication id, the second message is considered a duplicate, and the message will not be delivered if it is within the 5 minute deduplication interval.

If you have some custom logic that needs to be used to generate the deduplication id, you can register your own custom deduplicator. The deduplicators are stored in the IoC container with the prefix `queue.sqs-fifo.deduplicator`. So, for example, the `unique` deduplicator is aliased to `queue.sqs-fifo.deduplicator.unique`.

Custom deduplicators are created by registering a new prefixed alias in the IoC. This alias should resolve to a new object instance that implements the `ShiftOneLabs\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator` contract. You can either define a new class that implements this contract, or you can create a new `ShiftOneLabs\LaravelSqsFifoQueue\Queue\Deduplicators\Callback` instance, which takes a `Closure` that performs the deduplication logic. The defined `Closure` should take two parameters: `$payload` and `$queue`, where `$payload` is the `json_encoded()` message to send to the queue, and `$queue` is the name of the queue to which the message is being sent. The generated id must not be more than 128 characters, and can contain alphanumeric characters and punctuation (``!"#$%&'()*+,-./:;<=>?@[\]^_`{|}~``).

So, for example, if you wanted to create a `random` deduplicator that would randomly select some jobs to be duplicates, you could add the following line in the `register()` method of your `AppServiceProvider`:

``` php
$this->app->bind('queue.sqs-fifo.deduplicator.random', function ($app) {
    return new \ShiftOneLabs\LaravelSqsFifoQueue\Queue\Deduplicators\Callback(function ($payload, $queue) {
        // Return the deduplication id generated for messages. Randomly 0 or 1.
        return mt_rand(0,1);
    });
}
```

Or, if you prefer to create a new class, your class would look like this:

``` php
namespace App\Deduplicators;

use ShiftOneLabs\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;

class Random implements Deduplicator
{
    public function generate($payload, $queue)
    {
        // Return the deduplication id generated for messages. Randomly 0 or 1.
        return mt_rand(0,1);
    }
}
```

And you could register that class in your `AppServiceProvider` like this:

``` php
$this->app->bind('queue.sqs-fifo.deduplicator.random', App\Deduplicators\Random::class);
```

With this alias registered, you could update the `deduplicator` key in your configuration to use the value `random`, or you could set the deduplicator on individual jobs by calling `withDeduplicator('random')` on the job.

## Contributing

Contributions are welcome. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email patrick@shiftonelabs.com instead of using the issue tracker.

## Credits

- [Patrick Carlo-Hickman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.txt) for more information.

[ico-version]: https://img.shields.io/packagist/v/shiftonelabs/laravel-sqs-fifo-queue.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/shiftonelabs/laravel-sqs-fifo-queue/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/shiftonelabs/laravel-sqs-fifo-queue.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/shiftonelabs/laravel-sqs-fifo-queue.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/shiftonelabs/laravel-sqs-fifo-queue.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/shiftonelabs/laravel-sqs-fifo-queue
[link-travis]: https://travis-ci.org/shiftonelabs/laravel-sqs-fifo-queue
[link-scrutinizer]: https://scrutinizer-ci.com/g/shiftonelabs/laravel-sqs-fifo-queue/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/shiftonelabs/laravel-sqs-fifo-queue
[link-downloads]: https://packagist.org/packages/shiftonelabs/laravel-sqs-fifo-queue
[link-author]: https://github.com/patrickcarlohickman
[link-contributors]: ../../contributors
