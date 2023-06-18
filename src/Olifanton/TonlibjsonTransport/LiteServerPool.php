<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

interface LiteServerPool
{
    public function get(): LiteServer;
}
