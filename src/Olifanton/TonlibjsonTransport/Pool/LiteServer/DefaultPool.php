<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool\LiteServer;

use Olifanton\TonlibjsonTransport\LiteServer;
use Olifanton\TonlibjsonTransport\LiteServerPool;

class DefaultPool implements LiteServerPool
{
    public function borrow(): LiteServer
    {
        // FIXME: Implement borrow() method.
    }

    public function return(LiteServer $client): void
    {
        // FIXME: Implement return() method.
    }
}
