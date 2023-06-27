<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;

interface Executor
{
    /**
     * Method MUST return a mutable loop instance each time it is called.
     */
    public function ensureLoop(): Loop;

    /**
     * @throws FutureException
     */
    public function createFuture(callable $onTick, int $timeout = 60): Future;
}
