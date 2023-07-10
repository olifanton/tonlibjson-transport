<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\Traits;

use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureTimeoutException;
use Olifanton\TonlibjsonTransport\Async\FutureState;

trait GenericFuture
{
    protected string $id = "";

    protected FutureState $state = FutureState::WAIT_TICK;

    protected mixed $result = null;

    protected int $maxWaitingTime = 0;

    protected int $pollStartedAt = 0;

    /**
     * @var callable(Channel, Loop)
     */
    protected $onTick;

    public function getId(): string
    {
        return $this->id;
    }

    public function getState(): FutureState
    {
        return $this->state;
    }

    protected static function createId(): string
    {
        return hash("md5", random_bytes(128));
    }

    protected function checkStateRet(): array
    {
        if ($this->state === FutureState::FULFILLED) {
            return [true, $this->result];
        }

        if ($this->state === FutureState::REJECTED) {
            return [true, null];
        }

        $this->pollStartedAt = time();

        return [false, null];
    }

    /**
     * @throws \Throwable
     */
    protected function postAwait(mixed $result): mixed
    {
        if ($result instanceof \Throwable) {
            throw $result;
        }

        $this->result = $result;
        $this->state = FutureState::FULFILLED;

        return $this->result;
    }

    protected function startPoll(): void
    {
        if (!$this->pollStartedAt) {
            $this->pollStartedAt = time();
        }
    }

    /**
     * @throws FutureTimeoutException
     */
    protected function checkWaitingTime(): void
    {
        if ($this->pollStartedAt + $this->maxWaitingTime <= time()) {
            throw new FutureTimeoutException(sprintf(
                "Future max waiting time reached: %d seconds",
                $this->maxWaitingTime,
            ));
        }
    }
}
