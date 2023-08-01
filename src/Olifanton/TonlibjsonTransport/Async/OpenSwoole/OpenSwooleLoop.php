<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\OpenSwoole;

use Olifanton\TonlibjsonTransport\Async\Loop;
use Olifanton\TonlibjsonTransport\Async\Traits\GenericLoop;
use Swoole\Coroutine\System;

class OpenSwooleLoop implements Loop
{
    use GenericLoop;

    private ?int $cid = null;

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        if (!$this->isRunning) {
            $this->isRunning = true;

            $this->cid = go(function() {
                while ($this->isRunning) {
                    $this->tickRoutine();
                    $this->sleep(500);
                }
            });
        }
    }

    public function stop(): void
    {
        if ($this->isRunning) {
            \OpenSwoole\Coroutine::cancel($this->cid);
        }

        $this->stopInternal();
    }

    public function sleep(int $milliseconds): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        System::usleep($milliseconds * 1000); // @phpstan-ignore-line
    }
}
