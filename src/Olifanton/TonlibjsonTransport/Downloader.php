<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException;
use Olifanton\TonlibjsonTransport\Helpers\Filesystem;
use Olifanton\TonlibjsonTransport\Helpers\HttpClientFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Downloader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private string $baseUrl = "https://github.com/ton-blockchain/ton/releases/download/v2023.06/";

    public function __construct(
        private readonly HttpMethodsClientInterface $httpClient,
        private readonly Filesystem $fs,
    ) {}

    public static function discovered(): self
    {
        return new self(HttpClientFactory::discovered(), new Filesystem());
    }

    /**
     * @throws LibraryLocationException
     * @throws \Http\Client\Exception
     */
    public function download(string $targetDirectory, ?Platform $platform = null): void
    {
        if (!$this->fs->isDirExists($targetDirectory)) {
            throw new \RuntimeException("Directory $targetDirectory is not exists");
        }

        $lib = $platform ? GenericLocator::getName($platform) : GenericLocator::locateName();
        $targetFile = Filesystem::normalizeDir($targetDirectory) . DIRECTORY_SEPARATOR . $lib;

        if ($this->fs->isFileExists($targetFile)) {
            throw new \RuntimeException("File $targetFile exists");
        }

        $url = $this->baseUrl . $lib;
        $this
            ->logger
            ?->info("Start downloading from " . $url);
        $response = $this->httpClient->get($url);
        $status = $response->getStatusCode();

        if (!($status >= 200 && $status < 299)) {
            throw new \RuntimeException("Bad HTTP response status: " . $status);
        }

        $expectedLength = (int)$response->getHeaderLine("Content-Length");

        if (!$expectedLength) {
            throw new \RuntimeException("Invalid Content-Length");
        }

        $this
            ->logger
            ?->info(sprintf(
                "Response with status %d received, content length: %d",
                $status,
                $expectedLength,
            ));
        $resultLength = $this->fs->copyStreamToFile($response->getBody()->detach(), $targetFile);

        if ($resultLength !== $expectedLength) {
            $this->fs->safeUnset($targetFile);
            throw new \RuntimeException(
                "Result file mismatch, expected $expectedLength bytes, $expectedLength received"
            );
        }

        $this
            ->logger
            ?->info(sprintf(
                "File %s successfully written",
                $targetFile,
            ));
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }
}
