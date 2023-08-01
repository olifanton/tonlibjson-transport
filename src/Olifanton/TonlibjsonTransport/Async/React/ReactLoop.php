<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\React;

use Olifanton\TonlibjsonTransport\Async\Loop;
use Olifanton\TonlibjsonTransport\Async\Traits\GenericLoop;
use React\EventLoop\TimerInterface;
use function React\Async\async;

class ReactLoop implements Loop
{
    use GenericLoop;

    private ?TimerInterface $timer = null;

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        if (!$this->isRunning) {
            $this->isRunning = true;
            $this->timer = \React\EventLoop\Loop::addPeriodicTimer(0.5, async(function () {
                $this->tickRoutine();
            }));
        }
    }

    public function stop(): void
    {
        if ($this->isRunning && $this->timer) {
            \React\EventLoop\Loop::cancelTimer($this->timer);
        }

        $this->stopInternal();
    }

    /**
     * @throws \Throwable
     */
    public function sleep(int $milliseconds): void
    {
        \React\Promise\Timer\sleep($milliseconds / 1000);
    }
}
