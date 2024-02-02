<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue;

use Aws\Sqs\SqsClient;
use BadMethodCallException;
use Bisnow\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

class SqsFifoQueue extends SqsQueue
{
    /**
     * The queue name suffix.
     */
    protected string $suffix;

    /**
     * The message group id of the fifo pipe in the queue.
     */
    protected string $group;

    /**
     * The driver to generate the deduplication id for the message.
     */
    protected string $deduplicator = '';

    /**
     * The flag to check if this queue is setup for delay.
     */
    protected bool $allowDelay;

    /**
     * Get the current version of laravel.
     */
    protected float $appVersion;

    /**
     * Create a new Amazon SQS queue instance.
     *
     * @param  SqsClient  $sqs
     * @param  string  $default
     * @param  string  $prefix
     * @param  string  $suffix
     * @param  string  $group
     * @param  string  $deduplicator
     * @param  bool  $allowDelay
     *
     * @return void
     */
    public function __construct(SqsClient $sqs, string $default, string $prefix = '', string $suffix = '', string $group = '', string $deduplicator = '', bool $allowDelay = false)
    {
        parent::__construct($sqs, $default, $prefix);

        $this->appVersion = floatval(app()->version());
        $this->suffix = $suffix;
        $this->group = $group;
        $this->deduplicator = $deduplicator;
        $this->allowDelay = $allowDelay;
    }

    /**
     * Set the underlying SQS instance.
     *
     * @param SqsClient $sqs
     *
     * @return self
     */
    public function setSqs(SqsClient $sqs): self
    {
        $this->sqs = $sqs;

        return $this;
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string|null  $queue
     * @param  array  $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $message = [
            'QueueUrl' => $this->getQueue($queue), 'MessageBody' => $payload, 'MessageGroupId' => $this->getMeta($payload, 'group', $this->group),
        ];

        if (($deduplication = $this->getDeduplicationId($payload, $queue)) !== false) {
            $message['MessageDeduplicationId'] = $deduplication;
        }

        $response = $this->sqs->sendMessage($message);

        return $response->get('MessageId');
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * SQS FIFO queues do not allow per-message delays, but the queue itself
     * can be configured to delay the message. If this queue is setup for
     * delayed messages, push the job to the queue instead of throwing.
     *
     * @param  \DateTime|int  $delay
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        if ($this->allowDelay) {
            if ($this->appVersion > 8.24) {
                $this->setContainer(\app());
            }

            return $this->push($job, $data, $queue);
        }

        throw new BadMethodCallException('FIFO queues do not support per-message delays.');
    }

    /**
     * Get the queue or return the default.
     *
     * Laravel 7.x added support for a suffix, mainly to support Laravel Vapor.
     * Since SQS FIFO queues must end in ".fifo", supporting a suffix config
     * on these queues must be customized to work with the existing suffix.
     *
     * Additionally, this will provide support for the suffix config for older
     * versions of Laravel, in case anyone wants to use it.
     *
     * @param  string|null  $queue
     *
     * @return string
     */
    public function getQueue($queue)
    {
        $queue = $queue ?: $this->default;

        // Strip off the .fifo suffix to prepare for the config suffix.
        if ($this->appVersion > 6.0) {
            $queue = Str::beforeLast($queue, '.fifo');
        }

        if ($this->appVersion < 6.0) {
            $queue = Str::replaceLast('.fifo', '', $queue);
        }

        if (\is_bool($this->prefix)) {
            $this->prefix = '';
        }

        // Modify the queue name as needed and re-add the ".fifo" suffix.
        return filter_var($queue, FILTER_VALIDATE_URL) === false
            ? $this->suffixQueue($queue, $this->suffix)
            : $queue . '.fifo';
    }

    /**
     * Add the given suffix to the given queue name.
     *
     * @param  string  $queue
     * @param  string  $suffix
     * @return string
     */
    protected function suffixQueue($queue, $suffix = '')
    {
        return rtrim($this->prefix, '/') . '/' . Str::finish($queue, $suffix) . '.fifo';
    }

    /**
     * Get the deduplication id for the given driver.
     *
     * @param  string  $payload
     * @param  string  $queue
     *
     * @return string|bool
     *
     * @throws InvalidArgumentException
     */
    protected function getDeduplicationId($payload, $queue)
    {
        $driver = $this->getMeta($payload, 'deduplicator', $this->deduplicator);

        if (empty($driver)) {
            return false;
        }

        if ($this->container->bound($key = 'queue.sqs-fifo.deduplicator.' . $driver)) {
            $deduplicator = $this->container->make($key);

            if ($deduplicator instanceof Deduplicator) {
                return $deduplicator->generate($payload, $queue);
            }

            throw new InvalidArgumentException(sprintf('Deduplication method [%s] must resolve to a %s implementation.', $driver, Deduplicator::class));
        }

        throw new InvalidArgumentException(sprintf('Unsupported deduplication method [%s].', $driver));
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param  mixed  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     *
     * @return string
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws \Illuminate\Queue\InvalidPayloadException
     */
    protected function createPayload($job, $data = '', $queue = null): string
    {
        return parent::createPayload($job, $queue, $data);
    }

    /**
     * Create a payload array from the given job and data.
     *
     * @param  mixed  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     *
     * @return array
     */
    protected function createPayloadArray($job, $data = '', $queue = null): array
    {
        return array_merge(
            parent::createPayloadArray($job, $queue, $data),
            $this->getMetaPayload($job)
        );
    }

    /**
     * Get the meta data to add to the payload.
     *
     * @param  mixed  $job
     *
     * @return array
     */
    protected function getMetaPayload($job)
    {
        if (! is_object($job)) {
            return [];
        }

        $queueable = $job;

        if ($job instanceof SendQueuedNotifications) {
            $queueable = $job->notification;
        }

        if ($job instanceof SendQueuedMailable) {
            $queueable = $job->mailable;
        }

        return array_filter(
            [
                'group' => isset($queueable->messageGroupId) ? $queueable->messageGroupId : null,
                'deduplicator' => isset($queueable->deduplicator) ? $queueable->deduplicator : null,
            ],
            function ($value) {
                return $value !== null;
            }
        );
    }

    /**
     * Get additional meta from a payload string.
     *
     * @param  string  $payload
     * @param  string  $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    protected function getMeta($payload, $key, $default = null)
    {
        $payload = json_decode($payload, true);

        return Arr::get($payload, $key, $default);
    }
}
