<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Pool\Selector;
use Olifanton\TonlibjsonTransport\Tonlibjson\Client;
use Psr\Log\LoggerAwareInterface;

interface ClientPool extends LoggerAwareInterface
{
    public function borrow(): Client;

    public function return(Client $client): void;

    public function close(): void;
}
