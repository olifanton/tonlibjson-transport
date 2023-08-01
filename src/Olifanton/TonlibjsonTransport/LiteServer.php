<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use Olifanton\Ton\Marshalling\Json\Hydrator;

class LiteServer
{
    #[JsonMap]
    public readonly int $ip;

    #[JsonMap]
    public readonly int $port;

    #[JsonMap(serializer: JsonMap::SER_TYPE, param0: LiteServerId::class)]
    public readonly LiteServerId $id;

    /**
     * @throws \Olifanton\Ton\Marshalling\Exceptions\MarshallingException
     */
    public static function create(int $ip, int $port, array $id): self
    {
        return Hydrator::extract(self::class, [
            "ip" => $ip,
            "port" => $port,
            "id" => $id,
        ]);
    }

    public function toArray(): array
    {
        return [
            "ip" => $this->ip,
            "port" => $this->port,
            "id" => [
                "@type" => $this->id->type,
                "key" => $this->id->key,
            ],
        ];
    }
}
