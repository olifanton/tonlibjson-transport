<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL\Smc;

use Olifanton\Interop\Address;
use Olifanton\TonlibjsonTransport\TL\AccountAddress;
use Olifanton\TonlibjsonTransport\TL\DynamicTLObject;

final class Load extends DynamicTLObject
{
    public function __construct(Address|AccountAddress $address)
    {
        parent::__construct("smc.load", [
            "account_address" => $address instanceof Address ? new AccountAddress($address) : $address,
        ]);
    }
}
