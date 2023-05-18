<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Tonlibjson;

use FFI\CData;
use Olifanton\TonlibjsonTransport\Locator;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Olifanton\TonlibjsonTransport\VerbosityLevel;
use PHPUnit\Framework\TestCase;

class TonlibInstanceTest extends TestCase
{
    private function getInstance(): TonlibInstance
    {
        $locator = new Locator(BIN_LIB_PATH);
        $instance = new TonlibInstance($locator->locatePath());
        $instance->setVerbosityLevel(VerbosityLevel::FATAL);

        return $instance;
    }

    /**
     * @return void
     */
    public function testCreateAndDestroyClient(): void
    {
        $instance = $this->getInstance();
        $client = $instance->create();
        $this->assertInstanceOf(CData::class, $client->ptr);
        $instance->destroy($client);
    }
}
