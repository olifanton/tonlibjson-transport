<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\React;

use Fiber;
use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;
use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureTimeoutException;
use Olifanton\TonlibjsonTransport\Async\Future;
use Olifanton\TonlibjsonTransport\Async\FutureState;

class ReactFuture implements Future
{
    private ReactLoop $loop;

    private int $maxWaitingTime;

    private Resolver $resolver;

    private string $id;

    private FutureState $state = FutureState::WAIT_TICK;

    private Fiber $fiber;

    private int $pollStartedAt;

    private mixed $result;

    /**
     * @throws FutureException
     */
    public static function create(ReactLoop $loop, callable $onTick, int $maxWaitingTime = 60): ReactFuture
    {
        try {
            $instance = new self();
            $instance->loop = $loop;
            $instance->fiber = new Fiber(function () use ($onTick) {
                if (!$this->pollStartedAt) {
                    $this->pollStartedAt = time();
                }

                if ($this->state === FutureState::WAIT_TICK) {
                    try {
                        $this->state = FutureState::IN_POLL;
                        ($onTick)($this->resolver, $this->loop);
                        $this->state = FutureState::WAIT_TICK;

                        if ($this->pollStartedAt + $this->maxWaitingTime <= time()) {
                            throw new FutureTimeoutException(sprintf(
                                "Future max waiting time reached: %d seconds",
                                $this->maxWaitingTime,
                            ));
                        }
                    } catch (\Throwable $e) {
                        $this->state = FutureState::REJECTED;
                        $this->loop->removeFuture($this);
                        $this->resolver->resolve($e);
                    }
                }
            });
            $instance->maxWaitingTime = $maxWaitingTime;
            $instance->resolver = new Resolver($loop);
            $instance->id = hash("md5", random_bytes(128));
            $loop->addFuture($instance);

            return $instance;
        } catch (\Throwable $e) {
            throw new FutureException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function await(): mixed
    {
        if ($this->state === FutureState::FULFILLED) {
            return $this->result;
        }

        if ($this->state === FutureState::REJECTED) {
            return null;
        }

        $this->pollStartedAt = time();
        $result = $this->resolver->await();
        $this->loop->removeFuture($this);

        if ($result instanceof \Throwable) {
            throw $result;
        }

        $this->result = $result;
        $this->state = FutureState::FULFILLED;

        return $this->result;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getState(): FutureState
    {
        return $this->state;
    }

    public function getTickFiber(): Fiber
    {
        return $this->fiber;
    }

    public function cancel(): void
    {
        if ($this->state === FutureState::WAIT_TICK) {
            $this->loop->removeFuture($this);
        }
    }
}
