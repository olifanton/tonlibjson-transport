<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException;

interface Locator
{
    /**
     * Return current Platform (CPU and OS pair) if recognized.
     */
    public static function getPlatform(): ?Platform;

    /**
     * Returns binary library filename for specified Platform.
     *
     * @throws LibraryLocationException
     */
    public static function getName(Platform $platform): string;

    /**
     * Returns binary library filename for current CPU and OS.
     *
     * @throws LibraryLocationException
     */
    public static function locateName(): string;

    /**
     * Returns full binary library path for current CPU and OS.
     *
     * @throws LibraryLocationException
     */
    public function locatePath(): string;
}
