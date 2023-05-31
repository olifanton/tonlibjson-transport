<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Stubs;

use Olifanton\TonlibjsonTransport\Locator;
use Olifanton\TonlibjsonTransport\Platform;

class StubLocator implements Locator
{
    public static function getPlatform(): ?Platform
    {
        return Platform::LINUX_X64;
    }

    public static function getName(Platform $platform): string
    {
        return "tonlibjson-linux-x86_64.so";
    }

    public static function locateName(): string
    {
        return "tonlibjson-linux-x86_64.so";
    }

    public function locatePath(): string
    {
        return "/foo/bar/lib/tonlibjson-linux-x86_64.so";
    }
}
