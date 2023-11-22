<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL\Smc;

use Olifanton\TonlibjsonTransport\TL\DynamicTLObject;

final class MethodIdNumber extends DynamicTLObject
{
    public function __construct(int $number)
    {
        parent::__construct("smc.methodIdNumber", [
            "number" => $number,
        ]);
    }
}
