<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Pool\Selector;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ClientPool implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly TonlibInstance $tonlib,
        private readonly array $liteServers,
        private readonly Selector $selector,
    ) {}

    // @TODO
}
