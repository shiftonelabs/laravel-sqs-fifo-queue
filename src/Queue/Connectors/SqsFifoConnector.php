<?php

declare(strict_types=1);

namespace Bisnow\LaravelSqsFifoQueue\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Bisnow\LaravelSqsFifoQueue\SqsFifoQueue;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SqsFifoConnector extends SqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config): Queue
    {
        $config = $this->getDefaultConfiguration($config);

        if (! Str::endsWith($config['queue'], '.fifo')) {
            throw new InvalidArgumentException('FIFO queue name must end in ".fifo"');
        }

        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        $group = Arr::pull($config, 'group', 'default');
        $deduplicator = Arr::pull($config, 'deduplicator', 'unique');
        $allowDelay = (bool) Arr::pull($config, 'allow_delay', false);

        return new SqsFifoQueue(
            new SqsClient($config),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            Arr::get($config, 'suffix', ''),
            $group,
            $deduplicator,
            $allowDelay
        );
    }

    /**
     * Get the default configuration for SQS.
     *
     * @param  array  $config
     */
    protected function getDefaultConfiguration(array $config): array
    {
        return parent::getDefaultConfiguration($config);
    }
}
