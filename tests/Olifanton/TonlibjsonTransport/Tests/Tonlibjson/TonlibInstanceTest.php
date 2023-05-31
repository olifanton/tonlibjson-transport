<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Tonlibjson;

use FFI\CData;
use Olifanton\TonlibjsonTransport\GenericLocator;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Olifanton\TonlibjsonTransport\VerbosityLevel;
use PHPUnit\Framework\TestCase;

class TonlibInstanceTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    private function getInstance(): TonlibInstance
    {
        $locator = new GenericLocator(BIN_LIB_PATH);
        $instance = new TonlibInstance($locator->locatePath());
        $instance->setVerbosityLevel(VerbosityLevel::FATAL);

        return $instance;
    }

    /**
     * @throws \Throwable
     */
    public function testCreateAndDestroyClient(): void
    {
        $instance = $this->getInstance();
        $client = $instance->create();
        $this->assertInstanceOf(CData::class, $client->ptr);
        $instance->destroy($client);
    }
}
