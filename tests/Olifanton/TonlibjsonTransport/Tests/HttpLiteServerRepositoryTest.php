<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Http\Client\Common\HttpMethodsClientInterface;
use Mockery\MockInterface;
use Olifanton\Ton\Tests\Stubs\RawLs;
use Olifanton\TonlibjsonTransport\Exceptions\LiteServerFetchingException;
use Olifanton\TonlibjsonTransport\HttpLiteServerRepository;
use PHPUnit\Framework\TestCase;

class HttpLiteServerRepositoryTest extends TestCase
{
    private const CONFIG_URL = "https://example.config.ton/somenet.json";

    private HttpMethodsClientInterface & MockInterface $httpClientMock;

    protected function setUp(): void
    {
        $this->httpClientMock = \Mockery::mock(HttpMethodsClientInterface::class);  // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function getInstance(): HttpLiteServerRepository
    {
        return new HttpLiteServerRepository(
            $this->httpClientMock,
            self::CONFIG_URL,
        );
    }

    /**
     * @throws LiteServerFetchingException
     */
    public function testGetList(): void
    {
        $stubResponse = [
            "liteservers" => RawLs::get(),
        ];

        // @phpstan-ignore-next-line
        $this
            ->httpClientMock
            ->shouldReceive("get")
            ->with(self::CONFIG_URL)
            ->andReturn(
                new \Nyholm\Psr7\Response(body: json_encode($stubResponse)),
            );

        $instance = $this->getInstance();
        $servers = $instance->getList();

        $this->assertCount(3, $servers);

        $ls0 = $servers[0];
        $this->assertEquals(84478511, $ls0->ip);
        $this->assertEquals(19949, $ls0->port);
        $this->assertEquals("pub.ed25519", $ls0->id->type);
        $this->assertEquals("n4VDnSCUuSpjnCyUk9e3QOOd6o0ItSWYbTnW3Wnn8wk=", $ls0->id->key);
    }
}
