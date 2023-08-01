<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\Traits;

use Olifanton\TonlibjsonTransport\Async\FutureState;
use Olifanton\TonlibjsonTransport\Async\Tickable;

trait GenericLoop
{
    protected bool $isRunning = false;

    /**
     * @var callable|null
     */
    protected $onTick = null;

    /**
     * @var Tickable[]
     */
    protected array $futures = [];

    public function onTick(callable $onTick): void
    {
        $this->onTick = $onTick;
    }

    public function addFuture(Tickable $future): void
    {
        $this->futures[$future->getId()] = $future;
    }

    public function removeFuture(Tickable $future): void
    {
        $id = $future->getId();

        if (isset($this->futures[$id])) {
            unset($this->futures[$id]);
        }
    }

    /**
     * @throws \Throwable
     */
    protected function tickRoutine(): void
    {
        foreach ($this->futures as $future) {
            $state = $future->getState();

            if ($state === FutureState::WAIT_TICK) {
                $future->tick($this);
            } elseif ($state === FutureState::FULFILLED) {
                $this->removeFuture($future);
            }
        }

        if ($this->onTick) {
            ($this->onTick)();
        }
    }

    protected function stopInternal(): void
    {
        if ($this->isRunning) {
            $this->isRunning = false;

            foreach ($this->futures as $future) {
                $future->cancel();
            }

            $this->futures = [];
        }
    }
}
