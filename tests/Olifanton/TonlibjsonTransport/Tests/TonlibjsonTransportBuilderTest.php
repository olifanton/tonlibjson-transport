<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Olifanton\Ton\Tests\Stubs\StubLiteServerRepository;
use Olifanton\Ton\Tests\Stubs\StubLocator;
use Olifanton\Ton\Transport;
use Olifanton\TonlibjsonTransport\Exceptions\BuilderException;
use Olifanton\TonlibjsonTransport\Pool\RandomSelector;
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
        $instance = new TonlibjsonTransportBuilder(true);
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
                return \Mockery::mock(TonlibInstance::class);
            }
        })
            ->setLiteServerRepository(new StubLiteServerRepository())
            ->setConfigUrl("https://example.com/example.json")
            ->setLiteServers((new StubLiteServerRepository())->getList())
            ->setLocator(new StubLocator())
            ->setPoolSelector(new RandomSelector());
        $transport = $instance->build();
        $this->assertInstanceOf(Transport::class, $transport);
    }
}
