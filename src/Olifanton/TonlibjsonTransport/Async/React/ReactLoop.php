<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\React;

use Olifanton\TonlibjsonTransport\Async\Loop;
use Olifanton\TonlibjsonTransport\Async\Traits\GenericLoop;

class ReactLoop implements Loop
{
    use GenericLoop;

    private ?\React\Promise\Promise $loopPromise = null;

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        if (!$this->isRunning) {
            $this->isRunning = true;

            $this->loopPromise = \React\Async\async(function () {
                while ($this->isRunning) {
                    $this->tickRoutine();
                    $this->sleep(500);
                }
            })();
        }
    }

    public function stop(): void
    {
        if ($this->isRunning) {
            $this->loopPromise->cancel();
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
