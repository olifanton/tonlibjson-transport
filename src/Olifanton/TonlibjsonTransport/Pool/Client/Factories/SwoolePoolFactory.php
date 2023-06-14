<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool\Client\Factories;

use Olifanton\TonlibjsonTransport\ClientPool;
use Olifanton\TonlibjsonTransport\ClientPoolFactory;
use Olifanton\TonlibjsonTransport\Pool\Client\SwoolePool;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;

class SwoolePoolFactory implements ClientPoolFactory
{
    public function getPool(TonlibInstance $tonlib): ClientPool
    {
        return new SwoolePool($tonlib);
    }
}
