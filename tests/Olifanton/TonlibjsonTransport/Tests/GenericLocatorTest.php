<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Olifanton\TonlibjsonTransport\GenericLocator;
use Olifanton\TonlibjsonTransport\Platform;
use PHPUnit\Framework\TestCase;

class GenericLocatorTest extends TestCase
{
    private function getInstance(): GenericLocator
    {
        return new GenericLocator("/foo/bar");
    }

    /**
     * @throws \Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException
     */
    public function testGetName(): void
    {
        $cases = [
            [
                Platform::LINUX_X64,
                "tonlibjson-linux-x86_64.so"
            ],
            [
                Platform::LINUX_ARM,
                "tonlibjson-linux-arm64.so",
            ],
            [
                Platform::MAC_APPLE_SILICON,
                "tonlibjson-mac-arm64.dylib",
            ],
            [
                Platform::MAC_INTEL,
                "tonlibjson-mac-x86-64.dylib",
            ],
            [
                Platform::WIN_X64,
                "tonlibjson.dll"
            ]
        ];

        foreach ($cases as [$platform, $expected]) {
            $this->assertEquals($expected, $this->getInstance()::getName($platform), $platform->name);
        }
    }

    /**
     * @throws \Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException
     */
    public function testLocateName(): void
    {
        $this->assertStringContainsString("tonlibjson", $this->getInstance()::locateName());
    }
}
