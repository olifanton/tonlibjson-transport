<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Brick\Math\BigNumber;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\AddressState;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ResponseStack;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Transport;
use Olifanton\TonlibjsonTransport\Models\LiteServer;
use Olifanton\TonlibjsonTransport\Pool\Selector;
use Olifanton\TypedArrays\Uint8Array;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TonlibjsonTransport implements Transport, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param LiteServer[] $liteServers
     */
    public function __construct(
        private readonly ClientPool $pool,
        private readonly Selector $selector,
        private readonly array $liteServers,
    ) {}

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function runGetMethod(Contract|Address $contract, string $method, array $stack = []): ResponseStack
    {
        // TODO: Implement runGetMethod() method.
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function send(Uint8Array|string|Cell $boc): void
    {
        // TODO: Implement send() method.
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function sendMessage(ExternalMessage $message, Uint8Array $secretKey): void
    {
        // TODO: Implement sendMessage() method.
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function estimateFee(Address $address, string|Cell $body, string|Cell|null $initCode = null, string|Cell|null $initData = null): BigNumber
    {
        // TODO: Implement estimateFee() method.
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function getConfigParam(int $configParamId): Cell
    {
        // TODO: Implement getConfigParam() method.
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function getState(Address $address): AddressState
    {
        // TODO: Implement getState() method.
    }
}
