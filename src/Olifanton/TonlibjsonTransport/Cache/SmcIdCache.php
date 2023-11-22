<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Cache;

use Olifanton\Interop\Address;
use Olifanton\TonlibjsonTransport\TL\Smc\Load;
use Olifanton\TonlibjsonTransport\TonlibjsonTransport;

final class SmcIdCache
{
    private static array $items = [];

    public static function getId(Address|string $address): ?int
    {
        return self::$items[is_string($address) ? $address : self::key($address)] ?? null;
    }

    public static function setId(Address|string $address, int $id): void
    {
        self::$items[is_string($address) ? $address : self::key($address)] = $id;
    }

    public static function clean(): void
    {
        self::$items = [];
    }

    /**
     * @throws \Olifanton\TonlibjsonTransport\Exceptions\LiteServerError
     * @throws \Olifanton\TonlibjsonTransport\Exceptions\TonlibjsonTransportException
     */
    public static function ensure(Address $address, TonlibjsonTransport $transport): int
    {
        $key = self::key($address);
        $id = self::getId($key);

        if (!$id) {
            $id = $transport->execute(new Load($address))["id"];
            self::setId($key, $id);
        }

        return $id;
    }

    private static function key(Address $address): string
    {
        return $address->toString(isUserFriendly: false);
    }
}
