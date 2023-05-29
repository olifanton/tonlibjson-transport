<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool;

use Olifanton\TonlibjsonTransport\Models\LiteServer;

class RandomSelector implements Selector
{
    public function select(array $liteServers): LiteServer
    {
        if (empty($liteServers)) {
            throw new \RuntimeException("Empty liteservers list");
        }

        return $liteServers[array_rand($liteServers)];
    }
}
