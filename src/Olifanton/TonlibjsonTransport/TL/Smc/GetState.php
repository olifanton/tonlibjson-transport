<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL\Smc;

use Olifanton\TonlibjsonTransport\TL\DynamicTLObject;

final class GetState extends DynamicTLObject
{
    public function __construct(int $id)
    {
        parent::__construct(
            "smc.getState",
            [
                "id" => $id,
            ]
        );
    }
}
