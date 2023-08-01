<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

interface FutureResolver
{
    public function resolve(mixed $value): void;
}
