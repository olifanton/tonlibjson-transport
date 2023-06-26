<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL;

use Olifanton\Interop\Address;

class GetAccountState extends DynamicTLObject
{
    public function __construct(Address $address)
    {
        parent::__construct("getAccountState", [
            "account_address" => new DynamicTLObject("accountAddress", ["account_address" => $address->toString()]),
        ]);
    }
}
