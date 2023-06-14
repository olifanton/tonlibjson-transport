<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool\Client;

use Olifanton\TonlibjsonTransport\ClientPool;
use Olifanton\TonlibjsonTransport\Tonlibjson\Client;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Psr\Log\LoggerAwareTrait;
use Swoole\Coroutine\System;

/**
 * Swoole client pool.
 */
class SwoolePool implements ClientPool
{
    use LoggerAwareTrait;
    use CommonPool;

    public const BUSY_MODE_WAIT = 0;

    public const BUSY_MODE_FAILED = 1;

    private int $maxWaitMilliseconds = 30000;

    public function __construct(
        private readonly TonlibInstance $tonlib,
        private readonly int $maxPoolSize = 10,
        private readonly int $busyMode = self::BUSY_MODE_WAIT,
    ) {}

    public function borrow(): Client
    {
        if ($this->isClosed) {
            throw new \RuntimeException("Pool already closed");
        }

        if (!$this->isHaveFree($this->maxPoolSize)) {
            switch ($this->busyMode) {
                case self::BUSY_MODE_WAIT:
                    $waitTimeStart = microtime(true);
                    $waitUntil = $waitTimeStart + $this->maxWaitMilliseconds / 1000;

                    $this
                        ->logger
                        ?->debug("Start waiting free client, timeout " . $this->maxWaitMilliseconds . " ms");

                    /** @noinspection PhpConditionAlreadyCheckedInspection */
                    do {
                        System::sleep($this->getTickTimeout());

                        if (microtime(true) > $waitUntil) {
                            throw new \RuntimeException(
                                "Maximum wait time reached " . $this->maxWaitMilliseconds . " ms"
                            );
                        }
                    } while (!$this->isHaveFree($this->maxPoolSize)); // @phpstan-ignore-line
                    break; // @phpstan-ignore-line
                    // Note: Inspections disabled beacause PhpStorm and phpstan don't know about Swoole

                case self::BUSY_MODE_FAILED:
                    throw new \RuntimeException("All clients in the pool are busy");
            }
        }

        $client = $this->getFreeClient($this->maxPoolSize);
        $this
            ->logger
            ?->debug("Client borrowed, id " . $client->id);

        return $client;
    }

    public function setMaxWaitMilliseconds(int $maxWaitMilliseconds): self
    {
        $this->maxWaitMilliseconds = $maxWaitMilliseconds;

        return $this;
    }

    protected function getTickTimeout(): int
    {
        return 1;
    }
}
