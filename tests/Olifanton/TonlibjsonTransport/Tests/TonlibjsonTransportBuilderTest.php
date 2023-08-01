<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Olifanton\Ton\Tests\Stubs\RawLs;
use Olifanton\Ton\Tests\Stubs\StubLocator;
use Olifanton\Ton\Transport;
use Olifanton\TonlibjsonTransport\Exceptions\BuilderException;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Olifanton\TonlibjsonTransport\TonlibjsonTransportBuilder;
use PHPUnit\Framework\TestCase;

class TonlibjsonTransportBuilderTest extends TestCase
{
    /**
     * @throws BuilderException
     */
    public function testCreateWithDefaults(): void
    {
        $instance = new TonlibjsonTransportBuilder();
        $instance->setLibDirectory(BIN_LIB_PATH);
        $transport = $instance->build();
        $this->assertInstanceOf(Transport::class, $transport);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateComplex(): void
    {
        $instance = (new class extends TonlibjsonTransportBuilder {
            protected function createTonlib(): TonlibInstance
            {
                $mock = \Mockery::mock(TonlibInstance::class); // @phpstan-ignore-line
                $mock // @phpstan-ignore-line
                    ->shouldReceive("setVerbosityLevel");

                return $mock;
            }
        })
            ->setConfigUrl("https://example.com/example.json")
            ->setLiteServers(RawLs::mapped())
            ->setLocator(new StubLocator());
        $transport = $instance->build();
        $this->assertInstanceOf(Transport::class, $transport);
    }
}
