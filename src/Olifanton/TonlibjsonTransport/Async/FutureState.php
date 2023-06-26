<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Async;

enum FutureState : int
{
    case WAIT_TICK = 0;
    case IN_POLL = 1;
    case FULFILLED = 2;
    case REJECTED = 3;
}
