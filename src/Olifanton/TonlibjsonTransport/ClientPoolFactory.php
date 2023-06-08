<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;

interface ClientPoolFactory
{
    public function getPool(TonlibInstance $tonlib): ClientPool;
}
