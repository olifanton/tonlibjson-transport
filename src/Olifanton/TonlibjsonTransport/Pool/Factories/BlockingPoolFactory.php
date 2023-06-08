<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool\Factories;

use Olifanton\TonlibjsonTransport\ClientPool;
use Olifanton\TonlibjsonTransport\ClientPoolFactory;
use Olifanton\TonlibjsonTransport\Pool\BlockingPool;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;

class BlockingPoolFactory implements ClientPoolFactory
{
    public function getPool(TonlibInstance $tonlib): ClientPool
    {
        return new BlockingPool($tonlib);
    }
}
