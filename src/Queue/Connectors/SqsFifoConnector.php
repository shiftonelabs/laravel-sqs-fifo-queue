<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Queue\Connectors\SqsConnector;
use ShiftOneLabs\LaravelSqsFifoQueue\SqsFifoQueue;

class SqsFifoConnector extends SqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if (!Str::endsWith($config['queue'], '.fifo')) {
            throw new InvalidArgumentException('FIFO queue name must end in ".fifo"');
        }

        if (!empty($config['key']) && !empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        // Pull the custom config options out of the config array sent to SqsClient.
        $group = Arr::pull($config, 'group', 'default');
        $deduplicator = Arr::pull($config, 'deduplicator', 'unique');
        $allowDelay = (bool)Arr::pull($config, 'allow_delay', false);

        return new SqsFifoQueue(
            new SqsClient($config),
            $config['queue'],
            $config['prefix'] ?? '',
            $config['suffix'] ?? '',
            (bool)($config['after_commit'] ?? null),
            $group,
            $deduplicator,
            $allowDelay
        );
    }
}
