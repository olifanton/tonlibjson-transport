<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

/**
 * Async loop
 */
interface Loop
{
    /**
     * Starts the loop.
     */
    public function run(): void;

    /**
     * Stops the loop.
     */
    public function stop(): void;

    /**
     * Sets a closure to be called on every tick of the loop.
     */
    public function onTick(callable $onTick): void;

    /**
     * Non-blocking sleep.
     */
    public function sleep(int $milliseconds): void;
}
