<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool\LiteServer;

use Olifanton\TonlibjsonTransport\LiteServer;
use Olifanton\TonlibjsonTransport\LiteServerPool;

class DefaultPool implements LiteServerPool
{
    /**
     * @param LiteServer[] $liteServers
     */
    public function __construct(
        protected readonly Selector $selector,
        protected readonly array $liteServers,
    ) {}

    public function get(): LiteServer
    {
        if (empty($this->liteServers)) {
            throw new \InvalidArgumentException("Empty liteservers list");
        }

        return $this->selector->select($this->liteServers);
    }
}
