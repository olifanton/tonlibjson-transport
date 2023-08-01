<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\React;

use Olifanton\TonlibjsonTransport\Async\Executor;
use Olifanton\TonlibjsonTransport\Async\Future;
use Olifanton\TonlibjsonTransport\Async\Loop;

class ReactExecutor implements Executor
{
    private ?ReactLoop $loop = null;

    public function ensureLoop(): Loop
    {
        if (!$this->loop) {
            $this->loop = new ReactLoop();
        }

        return $this->loop;
    }

    public function createFuture(callable $onTick, int $timeout = 60): Future
    {
        if (!$this->loop) {
            $this->ensureLoop();
        }

        return ReactFuture::create($this->loop, $onTick, $timeout);
    }
}
