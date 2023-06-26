<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;
use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureTimeoutException;
use OpenSwoole\Coroutine\Channel;

/**
 * @phpstan-type OnTickCallback callable(OpenSwoole\Coroutine\Channel): void
 */
class Future
{
    /**
     * @var callable<Channel>
     */
    private $onTick;

    private Channel $channel;

    private Loop $loop;

    private int $maxWaitingTime;

    private string $id;

    private FutureState $state = FutureState::WAIT_TICK;

    private mixed $result = null;

    private ?int $pollStartedAt = null;

    /**
     * @phpstan-param OnTickCallback $onTick
     * @throws FutureException
     */
    public static function create(Loop $loop, callable $onTick, int $maxWaitingTime = 60): self
    {
        try {
            $instance = new self();
            $instance->loop = $loop;
            $instance->onTick = $onTick;
            $instance->maxWaitingTime = $maxWaitingTime;
            $instance->channel = new Channel(1);
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
        if ($this->state === FutureState::WAIT_TICK) {
            go(function () {
                try {
                    $this->state = FutureState::IN_POLL;
                    ($this->onTick)($this->channel);
                    $this->state = FutureState::WAIT_TICK;

                    if (($this->pollStartedAt + $this->maxWaitingTime) <= time()) {
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
