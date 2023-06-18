<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Pool\LiteServer;

use Olifanton\Ton\Tests\Stubs\RawLs;
use Olifanton\TonlibjsonTransport\Pool\LiteServer\DefaultPool;
use Olifanton\TonlibjsonTransport\Pool\LiteServer\FirstServerSelector;
use PHPUnit\Framework\TestCase;

class DefaultPoolTest extends TestCase
{
    /**
     * @return DefaultPool
     * @throws \Throwable
     */
    private function getInstance(): DefaultPool
    {
        return new DefaultPool(
            new FirstServerSelector(),
            RawLs::mapped(),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testGet(): void
    {
        $ls = $this->getInstance()->get();
        $this->assertEquals(84478511, $ls->ip);
        $this->assertEquals(19949, $ls->port);
        $this->assertEquals("n4VDnSCUuSpjnCyUk9e3QOOd6o0ItSWYbTnW3Wnn8wk=", $ls->id->key);
    }
}
