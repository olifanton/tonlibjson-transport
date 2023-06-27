<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Async\OpenSwoole;

use Olifanton\TonlibjsonTransport\Async\FutureResolver;
use Olifanton\TonlibjsonTransport\Async\OpenSwoole\OpenSwooleFuture;
use Olifanton\TonlibjsonTransport\Async\OpenSwoole\OpenSwooleLoop;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\System;

class OpenSwooleLoopTest extends TestCase
{
    private int $errorReportingLevel;

    protected function setUp(): void
    {
        $this->errorReportingLevel = error_reporting();
        error_reporting(E_ERROR);
    }

    protected function tearDown(): void
    {
        error_reporting($this->errorReportingLevel);
    }

    /**
     * @throws \Throwable
     */
    public function testMultipleFutures(): void
    {
        \co::run(function() {
            $start = time();
            $loop = new OpenSwooleLoop();
            $loop->run();

            $log = [];
            $f0 = OpenSwooleFuture::create($loop, function (FutureResolver $resolver) use (&$log) {
                System::sleep(3);
                $log[] = "f0";
                $resolver->resolve("foo");
            });
            $f1 = OpenSwooleFuture::create($loop, function (FutureResolver $resolver) use (&$log) {
                System::sleep(2);
                $log[] = "f1";
                $resolver->resolve("bar");
            });
            $r0 = $f0->await();
            $r1 = $f1->await();
            $loop->stop();
            $end = time();
            $this->assertEquals("foo", $r0);
            $this->assertEquals("bar", $r1);

            $this->assertEquals(["f1", "f0"], $log);
            $this->assertGreaterThan($start, $end);
            $this->assertEquals(4, $end - $start); // 4, not 5 (3 + 2)
        });
    }

    /**
     * @throws \Throwable
     */
    public function testRejectedFuture(): void
    {
        \co::run(function() {
            $loop = new OpenSwooleLoop();
            $loop->run();

            $f = OpenSwooleFuture::create($loop, function () {
                throw new \RuntimeException("foo");
            });

            try {
                $f->await();
            } catch (\Throwable $e) {
                $this->assertEquals("foo", $e->getMessage());
            }

            $loop->stop();
        });
    }
}
