<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

enum Platform : string
{
    case WIN_X64 = "win_x64";
    case MAC_INTEL = "darwin_intel";
    case MAC_APPLE_SILICON = "darwin_arm";
    case LINUX_X64 = "linux_x64";
    case LINUX_ARM = "linux_arm";
}
