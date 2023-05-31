<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Exceptions\LiteServerFetchingException;
use Olifanton\TonlibjsonTransport\Models\LiteServer;

interface LiteServerRepository
{
    /**
     * @return LiteServer[]
     * @throws LiteServerFetchingException
     */
    public function getList(): array;
}
