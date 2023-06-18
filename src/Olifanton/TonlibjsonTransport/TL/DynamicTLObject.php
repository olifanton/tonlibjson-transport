<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\TL;

class DynamicTLObject extends TLObject
{
    public function __construct(string $type, protected readonly array $fields)
    {
        if (array_is_list($this->fields)) {
            throw new \InvalidArgumentException("Lists are not supported");
        }

        parent::__construct($type);
    }

    protected function toJson(): array
    {
        return $this->iterate($this->fields);
    }

    protected function iterate(array $fields): array
    {
        $result = [];

        foreach ($fields as $field => $value) {
            if (in_array($field, ["@type", "type", "@extra", "extra"])) {
                continue;
            }

            if ($value instanceof TLObject) {
                $result[$field] = $value;
                continue;
            }

            if (is_array($value)) {
                $result[$field] = $this->iterate($value);
                continue;
            }

            if (is_scalar($value)) {
                $result[$field] = $value;
                continue;
            }

            throw new \InvalidArgumentException(sprintf(
                "Unsupported type: %s, key: %s",
                gettype($value),
                $field,
            ));
        }

        return $result;
    }
}
