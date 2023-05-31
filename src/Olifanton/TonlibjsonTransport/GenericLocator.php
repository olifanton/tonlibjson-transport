<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException;
use Olifanton\TonlibjsonTransport\Helpers\Filesystem;

class GenericLocator implements Locator
{
    protected static array $map = [
        "tonlibjson-linux-x86_64.so" => [
            "os_family" => "linux",
            "arch" => ["x86_64", "amd64"],
            "platform" => Platform::LINUX_X64,
        ],
        "tonlibjson-linux-arm64.so" => [
            "os_family" => "linux",
            "arch" => ["arm64", "aarch64"],
            "platform" => Platform::LINUX_ARM,
        ],
        "tonlibjson-mac-arm64.dylib" => [
            "os_family" => "darwin",
            "arch" => ["arm64", "aarch64"],
            "platform" => Platform::MAC_APPLE_SILICON,
        ],
        "tonlibjson-mac-x86-64.dylib" => [ // @TODO: Check this case
            "os_family" => "darwin",
            "arch" => ["x86_64"],
            "platform" => Platform::MAC_INTEL,
        ],
        "tonlibjson.dll" => [ // @TODO: Check this case
            "os_family" => "windows",
            "arch" => ["x86_64"],
            "platform" => Platform::WIN_X64,
        ],
    ];

    /**
     * @var string|null
     */
    protected static ?string $libraryName = null;

    public function __construct(
        private string $basePath,
    )
    {
        $this->basePath = Filesystem::normalizeDir($basePath);
    }

    /**
     * @inheritDoc
     */
    public static function getPlatform(): ?Platform
    {
        [$osFamily, $arch] = self::getOsArchPair();

        foreach (self::$map as $libraryName => ["os_family" => $fOsFamily, "arch" => $fArch, "platform" => $fPlatform]) {
            /** @var string $fOsFamily */
            /** @var string[] $fArch */

            if ($osFamily === $fOsFamily && in_array($arch, $fArch)) {
                return $fPlatform;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public static function getName(Platform $platform): string
    {
        foreach (self::$map as $libraryName => ["platform" => $fPlatform]) {
            if ($fPlatform === $platform) {
                return $libraryName;
            }
        }

        throw new LibraryLocationException("Not found library for " . $platform->name);
    }

    /**
     * @inheritDoc
     */
    public static function locateName(): string
    {
        if (self::$libraryName) {
            return self::$libraryName;
        }

        [$osFamily, $arch] = self::getOsArchPair();

        foreach (self::$map as $libraryName => ["os_family" => $fOsFamily, "arch" => $fArch]) {
            /** @var string $fOsFamily */
            /** @var string[] $fArch */

            if ($osFamily === $fOsFamily && in_array($arch, $fArch)) {
                self::$libraryName = $libraryName;

                return $libraryName;
            }
        }

        throw new LibraryLocationException(sprintf(
            "Unable to find library for OS family \"%s\" and architecture \"%s\"",
            $osFamily,
            $arch,
        ));
    }

    /**
     * @inheritDoc
     */
    public function locatePath(): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . self::locateName();
    }

    /**
     * @return string[]
     */
    protected final static function getOsArchPair(): array
    {
        return [strtolower(PHP_OS_FAMILY), strtolower(php_uname("m"))];
    }
}
