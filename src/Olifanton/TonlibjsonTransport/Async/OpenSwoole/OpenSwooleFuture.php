<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\OpenSwoole;

use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;
use Olifanton\TonlibjsonTransport\Async\Future;
use Olifanton\TonlibjsonTransport\Async\FutureState;
use Olifanton\TonlibjsonTransport\Async\Loop;
use Olifanton\TonlibjsonTransport\Async\Tickable;
use Olifanton\TonlibjsonTransport\Async\Traits\GenericFuture;
use OpenSwoole\Coroutine\Channel;

class OpenSwooleFuture implements Future, Tickable
{
    use GenericFuture;

    private Channel $channel;

    private Resolver $resolver;

    private OpenSwooleLoop $loop;

    /**
     * @throws FutureException
     */
    public static function create(OpenSwooleLoop $loop, callable $onTick, int $maxWaitingTime = 60): self
    {
        try {
            $instance = new self();
            $instance->loop = $loop;
            $instance->onTick = $onTick;
            $instance->maxWaitingTime = $maxWaitingTime;
            $instance->channel = new Channel(1);
            $instance->resolver = new Resolver($instance->channel);
            $instance->id = self::createId();
            $loop->addFuture($instance);

            return $instance;
        } catch (\Throwable $e) {
            throw new FutureException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws \Throwable
     */
    public function await(): mixed
    {
        [$isRet, $ret] = $this->checkStateRet();

        if ($isRet) {
            return $ret;
        }

        $result = $this->channel->pop();
        $this->channel->close();
        $this->loop->removeFuture($this);

        return $this->postAwait($result);
    }

    public function tick(Loop $loop): void
    {
        $this->startPoll();

        if ($this->state === FutureState::WAIT_TICK) {
            go(function () use ($loop) {
                try {
                    $this->state = FutureState::IN_POLL;
                    ($this->onTick)($this->resolver, $loop);
                    $this->state = FutureState::WAIT_TICK;
                    $this->checkWaitingTime();
                } catch (\Throwable $e) {
                    $this->state = FutureState::REJECTED;
                    $this->loop->removeFuture($this);
                    $this->channel->push($e);
                }
            });
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getState(): FutureState
    {
        return $this->state;
    }

    public function cancel(): void
    {
        if ($this->state === FutureState::WAIT_TICK) {
            $this->channel->close();
            $this->loop->removeFuture($this);
        }
    }
}
