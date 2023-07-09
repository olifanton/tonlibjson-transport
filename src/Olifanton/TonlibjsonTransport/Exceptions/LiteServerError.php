<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Exceptions;

use JetBrains\PhpStorm\Pure;

class LiteServerError extends TonlibjsonTransportException {
    private array $result;

    #[Pure] public function __construct(string $message, int $code, array $result)
    {
        parent::__construct($message, $code);

        $this->result = $result;
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
