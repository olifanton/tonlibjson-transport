<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\React;

use Fiber;
use Olifanton\TonlibjsonTransport\Async\FutureState;
use Olifanton\TonlibjsonTransport\Async\Loop;

class ReactLoop implements Loop
{
    private bool $isRunning = false;

    /**
     * @var ReactFuture[]
     */
    private array $futures = [];

    /**
     * @var ?callable
     */
    private $onTick = null;

    /**
     * @var \Generator[]
     */
    private array $waiters = [];

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        if (!$this->isRunning) {
            $this->isRunning = true;

            (new Fiber(function () {
                while ($this->isRunning) {
                    foreach ($this->futures as $future) {
                        $state = $future->getState();

                        if ($state === FutureState::WAIT_TICK) {
                            $this->callFiber($future->getTickFiber());
                        } elseif ($state === FutureState::FULFILLED) {
                            $this->removeFuture($future);
                        }
                    }

                    if ($this->onTick) {
                        ($this->onTick)();
                    }

                    $this->sleep(500);
                    $this->callWaiters();
                }
            }))->start();
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

            foreach ($this->waiters as $awaiter) {
                $awaiter->send(true);
            }

            $this->waiters = [];
        }
    }

    public function onTick(callable $onTick): void
    {
        $this->onTick = $onTick;
    }

    /**
     * @throws \Throwable
     */
    public function sleep(int $milliseconds): void
    {
        $stop = microtime(true) + $milliseconds / 1000;

        while (microtime(true) < $stop) {
            $this->callWaiters();
            Fiber::suspend();
        }
    }

    public function addFuture(ReactFuture $future)
    {
        $this->futures[$future->getId()] = $future;
    }

    public function removeFuture(ReactFuture $future): void
    {
        $id = $future->getId();

        if (isset($this->futures[$id])) {
            unset($this->futures[$id]);
        }
    }

    public function addAwaiter(\Generator $awaiter): void
    {
        $this->waiters[] = $awaiter;
    }

    public function removeAwaiter(\Generator $awaiter): void
    {
        $idx = array_search($awaiter, $this->waiters);

        if ($idx !== false) {
            unset($this->waiters[$idx]);
        }
    }

    /**
     * @throws \Throwable
     */
    private function callFiber(Fiber $fiber): mixed
    {
        if (!$fiber->isStarted()) {
            return $fiber->start();
        }

        if (!$fiber->isTerminated()) {
            return $fiber->resume();
        }

        return $fiber->getReturn();
    }

    private function callWaiters(): void
    {
        foreach ($this->waiters as $awaiter) {
            $awaiter->send(false);
        }
    }
}
