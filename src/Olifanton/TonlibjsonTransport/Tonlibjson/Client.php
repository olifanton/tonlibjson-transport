<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Tonlibjson;

use FFI\CData;

class Client
{
    public function __construct(
        public readonly CData $ptr,
    ) {}
}
