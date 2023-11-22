<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Brick\Math\BigInteger;
use Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException;
use Olifanton\Ton\Transports\Toncenter\ToncenterResponseStack;

class ResponseStack extends ToncenterResponseStack
{
    public static function parse(array $rawStack): ToncenterResponseStack
    {
        return parent::parse(self::serializeAsToncenterStack($rawStack));
    }

    /**
     * @throws ResponseStackParsingException
     */
    private static function serializeAsToncenterStack(array $rawStack): array
    {
        return array_map(static fn (array $entry) => self::serializeEntry($entry), $rawStack);
    }

    /**
     * @throws ResponseStackParsingException
     */
    private static function serializeEntry(array $entry): array
    {
        if (!isset($entry["@type"])) {
            throw new ResponseStackParsingException("Not a TVM element");
        }

        $type = $entry["@type"];

        if ($type === "tvm.stackEntryNumber") {
            return ["num", "0x" . BigInteger::fromBase($entry["number"]["number"], 10)->toBase(16)];
        }

        if ($type === "tvm.stackEntrySlice") {
            return ["cell", ["bytes" => $entry["slice"]["bytes"]]];
        }

        if ($type === "tvm.stackEntryCell") {
            return ["cell", ["bytes" => $entry["cell"]["bytes"]]];
        }

        if ($type === "tvm.stackEntryTuple") {
            return ["tuple", $entry["tuple"]];
        }

        if ($type === "tvm.stackEntryList") {
            return ["list", $entry["list"]];
        }

        throw new ResponseStackParsingException("Unknown type: " . $type);
    }
}
