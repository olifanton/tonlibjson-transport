<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\TL;

use Olifanton\TonlibjsonTransport\Platform;
use Olifanton\TonlibjsonTransport\TL\DynamicTLObject;
use PHPUnit\Framework\TestCase;

class DynamicTLObjectTest extends TestCase
{
    public function testSerialize(): void
    {
        $instance = new DynamicTLObject(
            "foo",
            [
                "@extra" => "1",
                "bar" => "baz",
                "foobar" => [
                    "f" => new DynamicTLObject("foo1", ["b" => "a"]),
                ],
            ],
        );
        $result = json_encode($instance, JSON_PRETTY_PRINT);
        $this->assertEquals(
            <<<JSON
            {
                "@type": "foo",
                "bar": "baz",
                "foobar": {
                    "f": {
                        "@type": "foo1",
                        "b": "a"
                    }
                }
            }
            JSON,
            $result,
        );
    }

    public function testSerializeBadType(): void
    {
        $instance = new DynamicTLObject(
            "foo",
            [
                "bad_type" => Platform::LINUX_X64,
            ],
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported type: object, key: bad_type");
        json_encode($instance);
    }

    public function testList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Lists are not supported");
        new DynamicTLObject(
            "foo",
            ["bar"],
        );
    }
}
