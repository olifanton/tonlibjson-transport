<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Helpers;

class Filesystem {
    public static function normalizeDir(string $dir): string
    {
        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir), DIRECTORY_SEPARATOR);
    }

    public function isDirExists(string $dirPath): bool
    {
        return file_exists($dirPath) && is_dir($dirPath);
    }

    public function isFileExists(string $filePath): bool
    {
        return file_exists($filePath);
    }

    public function safeUnset(string $filePath): void
    {
        if ($this->isFileExists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * @param resource $stream
     */
    public function copyStreamToFile($stream, string $targetFile): int
    {
        $writeHandle = fopen($targetFile, "x");
        $resultLength = stream_copy_to_stream($stream, $writeHandle);
        fclose($writeHandle);

        return $resultLength;
    }
}
