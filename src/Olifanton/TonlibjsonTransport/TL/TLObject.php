<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL;

abstract class TLObject implements \JsonSerializable
{
    public function __construct(
        protected readonly string $type,
    ) {}

    abstract protected function toJson(): array;

    public function jsonSerialize(): array
    {
        return array_merge([
            "@type" => $this->type,
        ], $this->toJson());
    }
}
