<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\React;

use Olifanton\TonlibjsonTransport\Async\FutureResolver;

class Resolver implements FutureResolver
{
    /**
     * @var callable
     */
    private $promiseResolve;

    public function __construct(callable $promiseResolve)
    {
        $this->promiseResolve = $promiseResolve;
    }

    public function resolve(mixed $value): void
    {
        ($this->promiseResolve)($value);
    }
}
