<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

interface Future
{
    /**
     * @throws \Throwable
     */
    public function await(): mixed;
}
