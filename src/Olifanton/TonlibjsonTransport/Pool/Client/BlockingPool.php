<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool\Client;

use Olifanton\TonlibjsonTransport\ClientPool;
use Olifanton\TonlibjsonTransport\Tonlibjson\Client;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Psr\Log\LoggerAwareTrait;

class BlockingPool implements ClientPool
{
    use LoggerAwareTrait;
    use CommonPool;

    public function __construct(
        private readonly TonlibInstance $tonlib,
    ) {}

    public function borrow(): Client
    {
        if (!$this->isHaveFree(1)) {
            throw new \RuntimeException("Client already borrowed");
        }

        $client = $this->getFreeClient(1);
        $this->borrowed[] = $client->id;

        return $client;
    }
}
