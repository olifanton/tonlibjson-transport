<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL;

use Olifanton\Interop\Address;

final class GetAccountState extends DynamicTLObject
{
    public function __construct(Address|AccountAddress $address)
    {
        if ($address instanceof Address) {
            $address = new AccountAddress($address);
        }

        parent::__construct("getAccountState", [
            "account_address" => $address,
        ]);
    }
}
