<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\React;

use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;
use Olifanton\TonlibjsonTransport\Async\Future;
use Olifanton\TonlibjsonTransport\Async\FutureState;
use Olifanton\TonlibjsonTransport\Async\Loop;
use Olifanton\TonlibjsonTransport\Async\Tickable;
use Olifanton\TonlibjsonTransport\Async\Traits\GenericFuture;
use React\Promise\Promise;

class ReactFuture implements Future, Tickable
{
    use GenericFuture;

    private ReactLoop $loop;

    private Resolver $resolver;

    private Promise $promise;

    /**
     * @throws FutureException
     */
    public static function create(ReactLoop $loop, callable $onTick, int $maxWaitingTime = 60): ReactFuture
    {
        try {
            $instance = new self();
            $instance->loop = $loop;
            $instance->onTick = $onTick;
            $instance->maxWaitingTime = $maxWaitingTime;
            $instance->promise = new Promise(function ($resolve) use ($instance) {
                $instance->resolver = new Resolver($resolve);
            });
            $instance->id = self::createId();
            $loop->addFuture($instance);

            return $instance;
        } catch (\Throwable $e) {
            throw new FutureException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function await(): mixed
    {
        [$isRet, $ret] = $this->checkStateRet();

        if ($isRet) {
            return $ret;
        }

        $result = \React\Async\await($this->promise);
        $this->loop->removeFuture($this);

        return $this->postAwait($result);
    }

    public function tick(Loop $loop): void
    {
        $this->startPoll();

        if ($this->state === FutureState::WAIT_TICK) {
            \React\Async\async(function () use ($loop) {
                try {
                    $this->state = FutureState::IN_POLL;
                    ($this->onTick)($this->resolver, $loop);
                    $this->state = FutureState::WAIT_TICK;
                    $this->checkWaitingTime();
                } catch (\Throwable $e) {
                    $this->state = FutureState::REJECTED;
                    $this->loop->removeFuture($this);
                    $this->resolver->resolve($e);
                }
            })();
        }
    }

    public function cancel(): void
    {
        if ($this->state === FutureState::WAIT_TICK) {
            $this->loop->removeFuture($this);
        }
    }
}
