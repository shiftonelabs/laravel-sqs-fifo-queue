<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue;

use Aws\Sqs\SqsClient;
use BadMethodCallException;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Queue\SqsQueue;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use ShiftOneLabs\LaravelSqsFifoQueue\Contracts\Queue\Deduplicator;

class SqsFifoQueue extends SqsQueue
{
    /**
     * The queue name suffix.
     *
     * This property was made protected in Laravel 10x. The redefinition
     * here can be removed when support for < Laravel 10x is dropped.
     *
     * @var string
     */
    protected $suffix;

    /**
     * The message group id of the fifo pipe in the queue.
     *
     * @var string
     */
    protected $group;

    /**
     * The driver to generate the deduplication id for the message.
     *
     * @var string
     */
    protected $deduplicator;

    /**
     * The flag to check if this queue is setup for delay.
     *
     * @var bool
     */
    protected $allowDelay;

    /**
     * Create a new Amazon SQS queue instance.
     *
     * @param  \Aws\Sqs\SqsClient  $sqs
     * @param  string  $default
     * @param  string  $prefix
     * @param  string  $suffix
     * @param  bool  $dispatchAfterCommit
     * @param  string  $group
     * @param  string  $deduplicator
     * @param  bool  $allowDelay
     *
     * @return void
     */
    public function __construct(SqsClient $sqs, $default, $prefix = '', $suffix = '', $dispatchAfterCommit = false, $group = '', $deduplicator = '', $allowDelay = false)
    {
        parent::__construct($sqs, $default, $prefix, $suffix, $dispatchAfterCommit);

        /**
         * The suffix property on SqsQueue was not made protected until Laravel 10x.
         * Since it is private on the parent class, the parent constructor will
         * not set the property on this class, so we must do it manually.
         */
        $this->suffix = $suffix;
        $this->group = $group;
        $this->deduplicator = $deduplicator;
        $this->allowDelay = $allowDelay;
    }

    /**
     * Set the underlying SQS instance.
     *
     * @param  \Aws\Sqs\SqsClient  $sqs
     *
     * @return \ShiftOneLabs\LaravelSqsFifoQueue\SqsFifoQueue
     */
    public function setSqs(SqsClient $sqs)
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
     * Push a new job onto the queue after (n) seconds.
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
            return $this->push($job, $data, $queue);
        }

        throw new BadMethodCallException('FIFO queues do not support per-message delays.');
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

        if ($this->container->bound($key = 'queue.sqs-fifo.deduplicator.'.$driver)) {
            $deduplicator = $this->container->make($key);

            if ($deduplicator instanceof Deduplicator) {
                return $deduplicator->generate($payload, $queue);
            }

            throw new InvalidArgumentException(sprintf('Deduplication method [%s] must resolve to a %s implementation.', $driver, Deduplicator::class));
        }

        throw new InvalidArgumentException(sprintf('Unsupported deduplication method [%s].', $driver));
    }

    /**
     * Create a payload array from the given job and data.
     *
     * @param  string|object  $job
     * @param  string  $queue
     * @param  mixed  $data
     *
     * @return array
     */
    protected function createPayloadArray($job, $queue, $data = '')
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
        if (!is_object($job)) {
            return [];
        }

        if ($job instanceof SendQueuedNotifications) {
            $queueable = $job->notification;
        } elseif ($job instanceof SendQueuedMailable) {
            $queueable = $job->mailable;
        } else {
            $queueable = $job;
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
