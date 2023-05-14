<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

enum VerbosityLevel : int
{
    case FATAL = 0;
    case ERROR = 1;
    case WARNING = 2;
    case INFO = 3;
    case DEBUG = 4;
}
