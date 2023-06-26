<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Async;

use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;
use Olifanton\TonlibjsonTransport\Async\Future;
use Olifanton\TonlibjsonTransport\Async\Loop;
use OpenSwoole\Coroutine\Channel;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\System;

class LoopTest extends TestCase
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
     * @throws FutureException|\Throwable
     */
    public function testComplex(): void
    {
        \co::run(function() {
            (function() {
                $start = time();
                $loop = new Loop();
                $loop->run();

                $log = [];
                $f0 = Future::create($loop, function (Channel $ch) use (&$log) {
                    System::sleep(3);
                    $log[] = "f0";
                    $ch->push("foo");
                });
                $f1 = Future::create($loop, function (Channel $ch) use (&$log) {
                    System::sleep(2);
                    $log[] = "f1";
                    $ch->push("bar");
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
            })();

            (function(){
                $loop = new Loop();
                $loop->run();

                $f = Future::create($loop, function (Channel $ch) {
                    throw new \RuntimeException("foo");
                });

                try {
                    $f->await();
                } catch (\Throwable $e) {
                    $this->assertEquals("foo", $e->getMessage());
                }

                $loop->stop();
            })();
        });
    }
}
