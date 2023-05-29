<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool;

use Olifanton\TonlibjsonTransport\Models\LiteServer;

interface Selector
{
    /**
     * @param LiteServer[] $liteServers
     */
    public function select(array $liteServers): LiteServer;
}
