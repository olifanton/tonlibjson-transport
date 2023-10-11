<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Mockery\MockInterface;
use Olifanton\Ton\Tests\Stubs\RawLs;
use Olifanton\Ton\Tests\Stubs\StubLocator;
use Olifanton\Ton\Transport;
use Olifanton\TonlibjsonTransport\Async\Executor;
use Olifanton\TonlibjsonTransport\Exceptions\BuilderException;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Olifanton\TonlibjsonTransport\TonlibjsonTransportBuilder;
use PHPUnit\Framework\TestCase;

class TonlibjsonTransportBuilderTest extends TestCase
{
    private Executor|MockInterface $executorMock; // @phpstan-ignore-line

    protected function setUp(): void
    {
        $this->executorMock = \Mockery::mock(Executor::class);  // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @throws BuilderException
     */
    public function testCreateWithDefaults(): void
    {
        $instance = new TonlibjsonTransportBuilder(
            $this->executorMock,
            isMainnet: false,
        );
        $instance->setLibDirectory(BIN_LIB_PATH);
        $transport = $instance->build();
        $this->assertInstanceOf(Transport::class, $transport);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateComplex(): void
    {
        $instance = (new class($this->executorMock) extends TonlibjsonTransportBuilder {
            protected function createTonlib(): TonlibInstance
            {
                $mock = \Mockery::mock(TonlibInstance::class); // @phpstan-ignore-line
                $mock // @phpstan-ignore-line
                    ->shouldReceive("setVerbosityLevel");

                return $mock;  // @phpstan-ignore-line
            }
        })
            ->setConfig(file_get_contents(TEST_STUBDATA_DIR . "/testnet-config.json"))
            ->setConfigUrl("https://example.com/example.json")
            ->setLiteServers(RawLs::mapped())
            ->setLocator(new StubLocator());
        $transport = $instance->build();
        $this->assertInstanceOf(Transport::class, $transport);
    }
}
