<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

interface Tickable
{
    public function getState(): FutureState;

    public function tick(Loop $loop): void;

    public function getId(): string;

    public function cancel(): void;
}
