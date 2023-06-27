<?php

namespace Olifanton\TonlibjsonTransport\Async;

interface Loop
{
    public function run(): void;

    public function stop(): void;
}
