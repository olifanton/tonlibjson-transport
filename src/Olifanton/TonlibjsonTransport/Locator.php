<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException;

class Locator
{
    private array $map = [
        "tonlibjson-linux-x86_64.so" => [
            "os_family" => "linux",
            "arch" => ["x86_64", "amd64"],
        ],
        "tonlibjson-linux-arm64.so" => [
            "os_family" => "linux",
            "arch" => ["arm64", "aarch64"],
        ],
        "tonlibjson-mac-arm64.dylib" => [
            "os_family" => "darwmin",
            "arch" => ["arm64", "aarch64"], // @TODO Check
        ],
        "tonlibjson-mac-x86-64.dylib" => [
            "os_family" => "darwmin",
            "arch" => ["x86_64"],
        ],
        "tonlibjson.dll" => [
            "os_family" => "windows",
            "arch" => ["x86_64"],
        ],
    ];

    private ?string $libraryName = null;

    public function __construct(
        private readonly string $basePath,
    ) {}

    /**
     * @throws LibraryLocationException
     */
    public function locateName(): string
    {
        if ($this->libraryName) {
            return $this->basePath;
        }

        $osFamily = strtolower(PHP_OS_FAMILY);
        $arch = strtolower(php_uname("m"));

        foreach ($this->map as $libraryName => ["os_family" => $fOsFamily, "arch" => $fArch]) {
            /** @var string $fOsFamily */
            /** @var string[] $fArch */

            if ($osFamily === $fOsFamily && in_array($arch, $fArch)) {
                $this->libraryName = $libraryName;

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
     * @throws LibraryLocationException
     */
    public function locatePath(): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . $this->locateName();
    }
}
