<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL\Smc;

use Olifanton\TonlibjsonTransport\TL\DynamicTLObject;

final class MethodIdName extends DynamicTLObject
{
    public function __construct(string $name)
    {
        parent::__construct("smc.methodIdName", [
            "name" => $name,
        ]);
    }
}
