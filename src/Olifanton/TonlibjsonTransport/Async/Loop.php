<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

use Swoole\Coroutine\System;

class Loop
{
    private bool $isRunning = false;

    /**
     * @var Future[]
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
                    var_dump("T");
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

                    System::sleep(1);
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

    public function addFuture(Future $future): void
    {
        $this->futures[$future->getId()] = $future;
    }

    public function removeFuture(Future $future): void
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
