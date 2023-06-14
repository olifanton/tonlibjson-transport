<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool\LiteServer;

use Olifanton\TonlibjsonTransport\LiteServer;

interface Selector
{
    /**
     * @param LiteServer[] $liteServers
     */
    public function select(array $liteServers): LiteServer;
}
