<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\OpenSwoole;

use Olifanton\TonlibjsonTransport\Async\FutureState;
use Olifanton\TonlibjsonTransport\Async\Loop;
use Swoole\Coroutine\System;

class OpenSwooleLoop implements Loop
{
    private bool $isRunning = false;

    /**
     * @var OpenSwooleFuture[]
     */
    private array $futures = [];

    /**
     * @var ?callable
     */
    private $onTick = null;

    private ?int $cid = null;

    /**
     * @return void
     */
    public function run(): void
    {
        if (!$this->isRunning) {
            $this->isRunning = true;

            $this->cid = go(function() {
                while ($this->isRunning) {
                    foreach ($this->futures as $future) {
                        $state = $future->getState();

                        if ($state === FutureState::WAIT_TICK) {
                            $future->tick();
                        } elseif ($state === FutureState::FULFILLED) {
                            $this->removeFuture($future);
                        }
                    }

                    if ($this->onTick) {
                        ($this->onTick)();
                    }

                    /** @noinspection PhpUndefinedMethodInspection */
                    System::usleep(5000); // @phpstan-ignore-line
                }
            });
        }
    }

    public function stop(): void
    {
        if ($this->isRunning) {
            $this->isRunning = false;

            foreach ($this->futures as $future) {
                $future->cancel();
            }

            $this->futures = [];
            \OpenSwoole\Coroutine::cancel($this->cid);
        }
    }

    public function addFuture(OpenSwooleFuture $future): void
    {
        $this->futures[$future->getId()] = $future;
    }

    public function removeFuture(OpenSwooleFuture $future): void
    {
        $id = $future->getId();

        if (isset($this->futures[$id])) {
            unset($this->futures[$id]);
        }
    }

    public function onTick(callable $onTick): void
    {
        $this->onTick = $onTick;
    }
}
