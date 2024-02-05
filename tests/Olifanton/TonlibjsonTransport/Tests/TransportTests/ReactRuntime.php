<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\TransportTests;

use Olifanton\TransportTests\TestCase;

class ReactRuntime implements \Olifanton\TransportTests\Runtime
{
    private static ?ReactRuntime $instance = null;

    public function setUp(): void
    {
        // TODO: Implement setUp() method.
    }

    public function run(TestCase $case): void
    {
        // TODO: Implement run() method.
    }

    public function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    public static function create(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
