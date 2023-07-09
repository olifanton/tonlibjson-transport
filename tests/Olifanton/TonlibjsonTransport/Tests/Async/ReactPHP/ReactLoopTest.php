<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Async\ReactPHP;

use Olifanton\TonlibjsonTransport\Async\React\ReactFuture;
use Olifanton\TonlibjsonTransport\Async\React\ReactLoop;
use Olifanton\TonlibjsonTransport\Async\FutureResolver;
use Olifanton\TonlibjsonTransport\Async\Loop;
use PHPUnit\Framework\TestCase;

class ReactLoopTest extends TestCase
{
    protected function setUp(): void
    {
        \React\EventLoop\Loop::run();
    }

    protected function tearDown(): void
    {
        \React\EventLoop\Loop::stop();
    }

    /**
     * @throws \Throwable
     */
    public function testMultipleFutures(): void
    {
        $start = time();
        $loop = new ReactLoop();
        $loop->run();

        $log = [];
        $f0 = ReactFuture::create($loop, function (FutureResolver $resolver, Loop $loop) use (&$log) {
            $loop->sleep(3000);
            $log[] = "f0";
            $resolver->resolve("foo");
        });
        $f1 = ReactFuture::create($loop, function (FutureResolver $resolver, Loop $loop) use (&$log) {
            $loop->sleep(2000);
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
    }

    /**
     * @throws \Throwable
     */
    public function testRejectedFuture(): void
    {
        $loop = new ReactLoop();
        $loop->run();

        $f = ReactFuture::create($loop, function () {
            throw new \RuntimeException("foo");
        });

        try {
            $f->await();
        } catch (\Throwable $e) {
            $this->assertEquals("foo", $e->getMessage());
        }

        $loop->stop();
    }
}
