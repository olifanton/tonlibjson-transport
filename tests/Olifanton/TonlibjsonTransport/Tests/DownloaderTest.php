<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Http\Client\Common\HttpMethodsClientInterface;
use Mockery\MockInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Olifanton\TonlibjsonTransport\Downloader;
use Olifanton\TonlibjsonTransport\Helpers\Filesystem;
use Olifanton\TonlibjsonTransport\Platform;
use PHPUnit\Framework\TestCase;

class DownloaderTest extends TestCase
{
    private HttpMethodsClientInterface|MockInterface $httpClientMock; // @phpstan-ignore-line
    private Filesystem|MockInterface $filesystemMock; // @phpstan-ignore-line

    protected function setUp(): void
    {
        $this->httpClientMock = \Mockery::mock(HttpMethodsClientInterface::class); // @phpstan-ignore-line
        $this->filesystemMock = \Mockery::mock(Filesystem::class); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function getInstance(): Downloader
    {
        return new Downloader(
            $this->httpClientMock,
            $this->filesystemMock,
        );
    }

    /**
     * @throws \Http\Client\Exception
     * @throws \Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException
     */
    public function testDownload(): void
    {
        // @phpstan-ignore-next-line
        $this
            ->filesystemMock
            ->shouldReceive("isDirExists")
            ->with("/foo/bar")
            ->once()
            ->andReturnTrue();
        // @phpstan-ignore-next-line
        $this
            ->filesystemMock
            ->shouldReceive("isFileExists")
            ->with("/foo/bar/tonlibjson-linux-x86_64.so")
            ->once()
            ->andReturnFalse();
        // @phpstan-ignore-next-line
        $this
            ->httpClientMock
            ->shouldReceive("get")
            ->once()
            ->andReturnUsing(function () {
                return (new Psr17Factory())
                    ->createResponse()
                    ->withBody(Stream::create("123"))
                    ->withHeader("Content-Length", "3");
            });
        // @phpstan-ignore-next-line
        $this
            ->filesystemMock
            ->shouldReceive("copyStreamToFile")
            ->once()
            ->andReturn(3);

        $this->getInstance()->download("/foo/bar", Platform::LINUX_X64);
        $this->addToAssertionCount(1);
    }
}
