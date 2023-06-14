<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Stubs;

use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\TonlibjsonTransport\LiteServer;
use Olifanton\TonlibjsonTransport\LiteServerRepository;

class StubLiteServerRepository implements LiteServerRepository
{
    /**
     * @return LiteServer[]
     * @throws MarshallingException
     */
    public function getList(): array
    {
        return [
            LiteServer::create(84478511, 19949, [
                '@type' => 'pub.ed25519',
                'key' => 'n4VDnSCUuSpjnCyUk9e3QOOd6o0ItSWYbTnW3Wnn8wk=',
            ]),
        ];
    }
}
