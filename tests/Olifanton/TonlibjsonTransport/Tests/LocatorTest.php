<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Olifanton\TonlibjsonTransport\Locator;
use PHPUnit\Framework\TestCase;

class LocatorTest extends TestCase
{
    public function testGet(): void
    {
        $locator = new Locator(dirname(__DIR__, 4) . "/lib");

        var_dump($locator->locatePath());
    }
}
