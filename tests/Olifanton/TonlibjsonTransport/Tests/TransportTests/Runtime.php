<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\TransportTests;

use Olifanton\TransportTests\TestCase;

class Runtime implements \Olifanton\TransportTests\Runtime
{
    private static ?SwooleRuntime $swooleRuntime = null;

    private static ?ReactRuntime $reactRuntime = null;

    public static function create(): \Olifanton\TransportTests\Runtime
    {
        $runtimeName = getenv("TONLIB_RUN");

        if (!$runtimeName) {
            $runtimeName = "swoole";
        }

        return match ($runtimeName) {
            "swoole" => self::ensureSwoole(),
            "react" => self::ensureReact(),
            default => throw new \RuntimeException("Unknown TONLIB_RUN environment variable: " . $runtimeName),
        };
    }

    private static function ensureSwoole(): \Olifanton\TransportTests\Runtime
    {
        if (!self::$swooleRuntime) {
            self::$swooleRuntime = SwooleRuntime::create();
        }

        return self::$swooleRuntime;
    }

    private static function ensureReact(): \Olifanton\TransportTests\Runtime
    {
        if (!self::$reactRuntime) {
            self::$reactRuntime = ReactRuntime::create();
        }

        return self::$reactRuntime;
    }

    public function setUp(): void {}

    public function run(TestCase $case): void {}

    public function tearDown(): void {}
}
