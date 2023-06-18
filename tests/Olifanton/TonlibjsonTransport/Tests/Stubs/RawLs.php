<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Stubs;

use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\TonlibjsonTransport\LiteServer;

final class RawLs
{
    /**
     * @return array[]
     */
    public static function get(): array
    {
        return [
            [
                'ip' => 84478511,
                'port' => 19949,
                'id' => [
                    '@type' => 'pub.ed25519',
                    'key' => 'n4VDnSCUuSpjnCyUk9e3QOOd6o0ItSWYbTnW3Wnn8wk=',
                ],
            ],
            [
                'ip' => 84478479,
                'port' => 48014,
                'id' => [
                    '@type' => 'pub.ed25519',
                    'key' => '3XO67K/qi+gu3T9v8G2hx1yNmWZhccL3O7SoosFo8G0=',
                ],
            ],
            [
                'ip' => -2018135749,
                'port' => 53312,
                'id' => [
                    '@type' => 'pub.ed25519',
                    'key' => 'aF91CuUHuuOv9rm2W5+O/4h38M3sRm40DtSdRxQhmtQ=',
                ],
            ],
        ];
    }

    /**
     * @return LiteServer[]
     * @throws MarshallingException
     */
    public static function mapped(): array
    {
        return array_map(fn (array $ls) => LiteServer::create($ls["ip"], $ls["port"], $ls["id"]), self::get());
    }
}
