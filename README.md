# laravel-sqs-fifo-queue

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.txt)
[![Build Status][ico-github-actions]][link-github-actions]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This Laravel/Lumen package provides a queue driver for Amazon's SQS FIFO queues. While Laravel works with Amazon's SQS standard queues out of the box, FIFO queues are slightly different and are not handled properly by Laravel. That is where this package comes in.

## Versions

This package has been tested on Laravel 4.1 through Laravel 8.x, though it may continue to work on later versions as they are released. This section will be updated to reflect the versions on which the package has actually been tested.

## Install

Via Composer

``` bash
$ composer require shiftonelabs/laravel-sqs-fifo-queue
```

Once composer has been updated and the package has been installed, the service provider will need to be loaded.

#### Laravel 5.5+, 6.x, 7.x, 8.x (5.5, 5.6, 5.7, 5.8, 6.x, 7.x, 8.x)

This package uses auto package discovery. The service provider will automatically be registered.

#### Laravel 5.0 - 5.4

Open `config/app.php` and add following line to the providers array:

``` php
ShiftOneLabs\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider::class,
```

#### Laravel 4 (4.1, 4.2)

Open `app/config/app.php` and add following line to the providers array:

``` php
'ShiftOneLabs\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider',
```

#### Lumen 5, 6, 7, 8 (5.0, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 6.x, 7.x, 8.x)

Open `bootstrap/app.php` and add following line under the "Register Service Providers" section:

``` php
$app->register(ShiftOneLabs\LaravelSqsFifoQueue\LaravelSqsFifoQueueServiceProvider::class);
```

## Configuration

#### Laravel/Lumen 5.1+, 6.x, 7.x, 8.x (5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 6.x, 7.x, 8.x)

If using Lumen, create a `config` directory in your project root if you don't already have one. Next, copy `vendor/laravel/lumen-framework/config/queue.php` to `config/queue.php`.

Now, for both Laravel and Lumen, open `config/queue.php` and add the following entry to the `connections` array.

    'sqs-fifo' => [
        'driver' => 'sqs-fifo',
        'key' => env('SQS_KEY'),
        'secret' => env('SQS_SECRET'),
        'prefix' => env('SQS_PREFIX'),
        'suffix' => env('SQS_SUFFIX'),
        'queue' => 'your-queue-name',    // ex: queuename.fifo
        'region' => 'your-queue-region', // ex: us-east-2
        'group' => 'default',
        'deduplicator' => 'unique',
        'allow_delay' => env('SQS_ALLOW_DELAY'),
    ],

Example .env file:

    SQS_KEY=ABCDEFGHIJKLMNOPQRST
    SQS_SECRET=1a23bc/deFgHijKl4mNOp5qrS6TUVwXyz7ABCDef
    SQS_PREFIX=https://sqs.us-east-2.amazonaws.com/123456789012
    SQS_ALLOW_DELAY=false

If you'd like this to be the default connection, also set `QUEUE_CONNECTION=sqs-fifo` in the `.env` file for >= 5.7, or `QUEUE_DRIVER=sqs-fifo` in the `.env` file for < 5.7.

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
        'allow_delay' => env('SQS_ALLOW_DELAY'),
    ],

Example .env file:

    SQS_KEY=ABCDEFGHIJKLMNOPQRST
    SQS_SECRET=1a23bc/deFgHijKl4mNOp5qrS6TUVwXyz7ABCDef
    SQS_PREFIX=https://sqs.us-east-2.amazonaws.com/123456789012
    SQS_ALLOW_DELAY=false

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
        'allow_delay' => false,
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
     * Set "prefix"/"suffix" and/or "queue" depending on version, as described for Laravel versions above
     * 'prefix' => 'your-prefix',
     * 'suffix' => 'your-suffix',
     * 'queue' => 'your-queue-name',
     */
    'region' => 'your-queue-region', // ex: us-east-2
    'group' => 'default',
    'deduplicator' => 'unique',
    'allow_delay' => false,
], 'sqs-fifo');

// Make this Capsule instance available globally via static methods... (optional)
$queue->setAsGlobal();

// Register the 'queue' alias in the Container, then register the SQS FIFO provider.
$app = $queue->getContainer();
$app->instance('queue', $queue->getQueueManager());
(new LaravelSqsFifoQueueServiceProvider($app))->register();
```

#### Credentials

The `key` and `secret` config options may be omitted if using one of the alternative options for providing AWS credentials (e.g. using an AWS credentials file). More information about alternative options is available in the [AWS PHP SDK guide here](https://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html).

#### AWS STS Session Token

The `'token' => env('AWS_SESSION_TOKEN'),` config option may be added if you need to specify an AWS STS temporary session token in the config. This is needed for some specific environments, such as AWS Lambda.

#### Queue Suffix

The `suffix` config option is used to support queues for different environments without having to specify the environment suffix when using the queue. For example, if you have an `emails-staging.fifo` queue and an `emails-production.fifo` queue, you can set the `suffix` config to `-staging` or `-production` based on the environment, and your code can continue to use `emails.fifo` without needing to know the environment. So, `Job::dispatch()->onQueue('emails.fifo')` will use either the `emails-staging.fifo` or the `emails-production.fifo` queue, depending on the `suffix` defined in the config.

This functionality was introduced to plain SQS queues in Laravel 7.x, primarily to support Laravel Vapor. More details are available on [the PR for the feature](https://github.com/laravel/framework/pull/31784).

There are two differences between the standard Laravel implementation and this one:

- This package adds support for queue suffixes all the way back to Laravel 5.1.
- SQS FIFO queues must end with a `.fifo` suffix. As seen in the example above, any `suffix` defined in the config will come before the required `.fifo` suffix. Do not specify `.fifo` in the suffix config or the queue name will not generate correctly.

#### Jobs & Database Transactions

Laravel 8.19.0 added in support for [the `after_commit` configuration option](https://laravel.com/docs/8.x/queues#jobs-and-database-transactions). When set to `true`, this option ensures that jobs that are queued during a database transaction are not actually dispatched until after the database transaction is committed. If the transaction is rolled back, the job will be discarded and not dispatched. If there are no active database transactions, the job will be dispatched immediately. This package has been updated to support this feature, but it does not add support for the feature to previous versions of Laravel (like the suffix did).

## Usage

For the most part, usage of this queue driver is the same as the built in queue drivers. There are, however, a few extra things to consider when working with Amazon's SQS FIFO queues.

#### Message Groups

In addition to being able to have multiple queue names for each connection, an SQS FIFO queue also allows one to have multiple "message groups" for each FIFO queue. These message groups are used to group related jobs together, and jobs are processed in FIFO order per group. This is important, as your queue performance may depend on being able to assign message groups properly. If you have 100 jobs in the queue, and they all belong to one message group, then only one queue worker will be able to process the jobs at a time. If, however, they can logically be split across 5 message groups, then you could have 5 queue workers processing the jobs from the queues (one per group). The FIFO ordering is per message group.

Currently, by default, all queued jobs will be lumped into one group, as defined in the configuration file. In the configuration provided above, all queued jobs would be sent as part of the `default` group. The group can be changed per job using the `onMessageGroup()` method, which will be explained more below.

The group id must not be empty, must not be more than 128 characters, and can contain alphanumeric characters and punctuation (``!"#$%&'()*+,-./:;<=>?@[\]^_`{|}~``).

In a future release, the message group will be able to be assigned to a function, like the deduplicator below.

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

SQS FIFO queues do not support per-message delays, only per-queue delays. The desired delay is defined on the queue itself when the queue is setup in the Amazon Console. Attempting to set a delay on a job sent to a FIFO queue will have no effect. In order to delay a job, you can `push()` the job to an SQS FIFO queue that has been defined with a delivery delay.

Since per-message delays are not supported, using the `later()` method to push a job to an SQS FIFO queue will throw a `BadMethodCallException` exception by default. However, this behavior can be changed using the `allow_delay` config option.

Setting the `allow_delay` config option to `true` for a queue will allow the `later()` method to push a job on that queue without an exception. However, the delay parameter sent to the `later()` method is completely ignored since the delay time is defined in SQS on the queue itself.

*Note: There is a bug in Laravel 5.4.36 - 5.6.35 that causes all queued `Mailable` objects that use the `\Illuminate\Bus\Queueable` trait to use the `later()` method. If you are affected by this, you will need to set the `allow_delay` config option to `true` for your queues, even if they don't allow delays. This will prevent the exception from being thrown.*

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

class ProcessCoin implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SqsFifoQueueable, SerializesModels;

    //
}
```

Usage:

``` php
dispatch(
    (new \App\Jobs\ProcessCoin())
        ->onMessageGroup('quarter')
        ->withDeduplicator('unique')
);
```

*Note: Laravel 4 does not normally support passing job instances to the queue, but this package does. The job instance will get converted to just the class name, so the queuing system will continue to work, but the message group and the deduplicator will be pulled off of the job instance before the conversion.*

Notification:

``` php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use ShiftOneLabs\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable, SqsFifoQueueable;

    //
}
```

Usage:

``` php
$user->notify(
    (new InvoicePaid($invoice))->onMessageGroup($invoice->id)
);
```

Mailable:

``` php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use ShiftOneLabs\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;

class OrderShipped extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SqsFifoQueueable;

    //
}
```

Usage:

``` php
Mail::to($request->user())
    ->cc($moreUsers)
    ->bcc($evenMoreUsers)
    ->queue(new OrderShipped($order)->onMessageGroup($order->number));
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
[ico-github-actions]: https://img.shields.io/github/actions/workflow/status/shiftonelabs/laravel-sqs-fifo-queue/.github/workflows/phpunit.yml?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/shiftonelabs/laravel-sqs-fifo-queue.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/shiftonelabs/laravel-sqs-fifo-queue.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/shiftonelabs/laravel-sqs-fifo-queue.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/shiftonelabs/laravel-sqs-fifo-queue
[link-github-actions]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/actions
[link-scrutinizer]: https://scrutinizer-ci.com/g/shiftonelabs/laravel-sqs-fifo-queue/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/shiftonelabs/laravel-sqs-fifo-queue
[link-downloads]: https://packagist.org/packages/shiftonelabs/laravel-sqs-fifo-queue
[link-author]: https://github.com/patrickcarlohickman
[link-contributors]: ../../contributors
