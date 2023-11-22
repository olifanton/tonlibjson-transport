<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL\Smc;

use Olifanton\TonlibjsonTransport\TL\DynamicTLObject;

final class RunGetMethod extends DynamicTLObject
{
    public function __construct(int $id, MethodIdNumber|MethodIdName|string|int $method, array $stack)
    {
        if (is_string($method)) {
            $method = new MethodIdName($method);
        }

        if (is_int($method)) {
            $method = new MethodIdNumber($method);
        }

        parent::__construct("smc.runGetMethod", [
            "id" => $id,
            "method" => $method,
            "stack" => $stack,
        ]);
    }
}
