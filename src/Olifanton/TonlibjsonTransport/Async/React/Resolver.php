<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\React;

use Olifanton\TonlibjsonTransport\Async\FutureResolver;

class Resolver implements FutureResolver
{
    private bool $isResolved = false;

    private mixed $value = null;

    private \Generator $awaiter;

    public function __construct(
        private readonly ReactLoop $loop,
    )
    {
        $this->awaiter = (function () {
            // FIXME ????
        })();
        $this->loop->addAwaiter($this->awaiter);
    }

    public function resolve(mixed $value): void
    {
        $this->isResolved = true;
        $this->value = $value;
        $this->loop->removeAwaiter($this->awaiter);
    }

    /**
     * @throws \Throwable
     */
    public function await(): mixed
    {
        $f = (new \Fiber(function () {
            if ($this->isResolved) {
                return $this->value;
            }

            if ($this->awaiter->current()) {
                \Fiber::suspend();
            }

            return $this->value;
        }));
        $f->start();

        while (true) {
            $this->loop->run();
            $this->awaiter->send(false);
        }

        return $this->value;
    }
}
