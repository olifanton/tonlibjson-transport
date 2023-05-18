<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Models;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use Olifanton\Ton\Marshalling\Json\Hydrator;

class LiteServerId
{
    #[JsonMap("@type")]
    public readonly string $type;

    #[JsonMap]
    public readonly string $key;

    /**
     * @throws \Olifanton\Ton\Marshalling\Exceptions\MarshallingException
     */
    public static function create(string $type, string $key): self
    {
        return Hydrator::extract(self::class, [
            "@type" => $type,
            "key" => $key,
        ]);
    }
}
