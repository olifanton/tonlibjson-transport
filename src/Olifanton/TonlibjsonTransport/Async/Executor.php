<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;

/**
 * Asynchronous executor
 */
interface Executor
{
    /**
     * Method MUST return a mutable loop instance each time it is called.
     */
    public function ensureLoop(): Loop;

    /**
     * @param callable(FutureResolver, Loop): mixed $onTick
     * @throws FutureException
     */
    public function createFuture(callable $onTick, int $timeout = 60): Future;
}
