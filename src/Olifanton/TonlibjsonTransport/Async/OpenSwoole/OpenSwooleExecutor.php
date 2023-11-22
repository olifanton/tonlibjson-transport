<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\OpenSwoole;

use Olifanton\TonlibjsonTransport\Async\Executor;
use Olifanton\TonlibjsonTransport\Async\Future;
use Olifanton\TonlibjsonTransport\Async\Loop;

class OpenSwooleExecutor implements Executor
{
    private ?OpenSwooleLoop $loop = null;

    public function __construct(
        private readonly int $interval = 500,
    ) {}

    /**
     * @inheritDoc
     */
    public function ensureLoop(): Loop
    {
        if (!$this->loop) {
            $this->loop = new OpenSwooleLoop($this->interval);
        }

        return $this->loop;
    }

    /**
     * @inheritDoc
     */
    public function createFuture(callable $onTick, int $timeout = 60): Future
    {
        if (!$this->loop) {
            $this->ensureLoop();
        }

        return OpenSwooleFuture::create($this->loop, $onTick, $timeout);
    }
}
