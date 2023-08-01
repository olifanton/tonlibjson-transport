<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async\OpenSwoole;

use Olifanton\TonlibjsonTransport\Async\FutureResolver;
use OpenSwoole\Coroutine\Channel;

class Resolver implements FutureResolver
{
    public function __construct(
        private readonly Channel $channel,
    ) {}

    public function resolve(mixed $value): void
    {
        $this->channel->push($value);
    }
}
