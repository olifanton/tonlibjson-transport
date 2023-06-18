<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool\LiteServer;

use Olifanton\TonlibjsonTransport\LiteServer;

class FirstServerSelector implements Selector
{
    /**
     * @param LiteServer[] $liteServers
     */
    public function select(array $liteServers): LiteServer
    {
        if (empty($liteServers)) {
            throw new \InvalidArgumentException("Empty liteservers list");
        }

        return $liteServers[array_key_first($liteServers)];
    }
}
