<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Exceptions\LiteServerFetchingException;

interface LiteServerRepository
{
    /**
     * @return LiteServer[]
     * @throws LiteServerFetchingException
     */
    public function getList(): array;
}
