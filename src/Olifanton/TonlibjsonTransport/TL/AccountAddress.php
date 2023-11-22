<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL;

use Olifanton\Interop\Address;

final class AccountAddress extends DynamicTLObject
{
    public function __construct(Address $address)
    {
        parent::__construct("accountAddress", ["account_address" => $address->toString()]);
    }
}
