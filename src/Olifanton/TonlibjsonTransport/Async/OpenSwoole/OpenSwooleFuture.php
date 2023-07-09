<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\OpenSwoole;

use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;
use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureTimeoutException;
use Olifanton\TonlibjsonTransport\Async\Future;
use Olifanton\TonlibjsonTransport\Async\FutureState;
use OpenSwoole\Coroutine\Channel;

class OpenSwooleFuture implements Future
{
    /**
     * @var callable(Channel)
     */
    private $onTick;

    private Channel $channel;

    private Resolver $resolver;

    private OpenSwooleLoop $loop;

    private int $maxWaitingTime;

    private string $id;

    private FutureState $state = FutureState::WAIT_TICK;

    private mixed $result = null;

    private ?int $pollStartedAt = null;

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
            $instance->id = hash("md5", random_bytes(128));
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
        if ($this->state === FutureState::FULFILLED) {
            return $this->result;
        }

        if ($this->state === FutureState::REJECTED) {
            return null;
        }

        $this->pollStartedAt = time();
        $result = $this->channel->pop();
        $this->channel->close();
        $this->loop->removeFuture($this);

        if ($result instanceof \Throwable) {
            throw $result;
        }

        $this->result = $result;
        $this->state = FutureState::FULFILLED;

        return $this->result;
    }

    public function tick(): void
    {
        if (!$this->pollStartedAt) {
            $this->pollStartedAt = time();
        }

        if ($this->state === FutureState::WAIT_TICK) {
            go(function () {
                try {
                    $this->state = FutureState::IN_POLL;
                    ($this->onTick)($this->resolver);
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
