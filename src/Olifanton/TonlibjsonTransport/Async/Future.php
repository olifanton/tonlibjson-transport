<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

interface Future
{
    public function await(): mixed;
}
