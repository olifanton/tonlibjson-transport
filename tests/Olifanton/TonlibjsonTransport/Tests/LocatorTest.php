<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException;
use Olifanton\TonlibjsonTransport\GenericLocator;
use Olifanton\TonlibjsonTransport\Platform;
use PHPUnit\Framework\TestCase;

class LocatorTest extends TestCase
{
    private ?Platform $platform = null;

    protected function setUp(): void
    {
        if (!$this->platform) {
            $this->platform = GenericLocator::getPlatform();

            if (!$this->platform) {
                throw new \RuntimeException(sprintf(
                    "Unsupported OS and/or architecture: %s (%s)",
                    PHP_OS_FAMILY,
                    php_uname("m"),
                ));
            }
        }
    }

    /**
     * @throws LibraryLocationException
     */
    public function testLocatePath(): void
    {
        $locator = new GenericLocator("/lib/");

        switch ($this->platform) {
            case Platform::LINUX_X64:
                $this->assertEquals(
                    "/lib/tonlibjson-linux-x86_64.so",
                    $locator->locatePath(),
                    Platform::LINUX_X64->name,
                );
                return;

            case Platform::WIN_X64:
                $this->assertEquals(
                    "\\lib\\tonlibjson.dll",
                    $locator->locatePath(),
                    Platform::WIN_X64->name,
                );
                return;

            case Platform::MAC_INTEL:
                $this->assertEquals(
                    "/lib/tonlibjson-mac-x86-64.dylib",
                    $locator->locatePath(),
                    Platform::MAC_INTEL->name,
                );
                break;

            case Platform::MAC_APPLE_SILICON:
                $this->assertEquals(
                    "/lib/tonlibjson-mac-arm64.dylib",
                    $locator->locatePath(),
                    Platform::MAC_APPLE_SILICON->name,
                );
                break;

            case Platform::LINUX_ARM:
                $this->assertEquals(
                    "/lib/tonlibjson-linux-arm64.so",
                    $locator->locatePath(),
                    Platform::LINUX_X64->name,
                );
                return;

            default:
                throw new \RuntimeException("Unknown case: " . $this->platform->name);
        }
    }
}
