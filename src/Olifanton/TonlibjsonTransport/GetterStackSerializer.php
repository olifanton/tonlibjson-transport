<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Marshalling\Tvm\Cell;
use Olifanton\Ton\Marshalling\Tvm\Number;
use Olifanton\Ton\Marshalling\Tvm\Slice;
use Olifanton\Ton\Marshalling\Tvm\TvmStackEntry;
use Olifanton\TonlibjsonTransport\TL\DynamicTLObject;

final class GetterStackSerializer
{
    /**
     * @param array|TvmStackEntry[]|DynamicTLObject[] $stack
     * @return array[]
     * @throws CellException
     * @throws SliceException
     */
    public static function serialize(array $stack): array
    {
        $result = [];

        foreach ($stack as $idx => $entry) {
            if ($entry instanceof TvmStackEntry) {
                if ($entry instanceof Cell) {
                    $result[] = new DynamicTLObject(
                        "tvm.Cell",
                        [
                            "data" => Bytes::bytesToBase64($entry->getData()->toBoc(false)),
                        ]
                    );
                    continue;
                }

                if ($entry instanceof Slice) {
                    $result[] = new DynamicTLObject(
                        "tvm.Slice",
                        [
                            "bytes" => Bytes::bytesToBase64(
                                (new Builder())->writeSlice($entry->getData())->cell()->toBoc(has_idx: false),
                            ),
                        ]
                    );
                    continue;
                }

                if ($entry instanceof Number) {
                    $n = $entry->getData();
                    $result[] = new DynamicTLObject(
                        "tvm.numberDecimal",
                        [
                            "number" => "0x" . (is_int($n) ? dechex($n) : $n->toBase(16)),
                        ],
                    );
                    continue;
                }

                throw new \RuntimeException("Not implemented serializer for " . $entry::class);
            } else if (is_array($entry) && array_is_list($entry)) {
                [$type, $data] = $entry;

                switch ($type) {
                    case "cell":
                        $result[] = new DynamicTLObject(
                            "tvm.Cell",
                            [
                                "data" => $data,
                            ]
                        );
                        break;

                    case "num":
                        $result[] = new DynamicTLObject(
                            "tvm.numberDecimal",
                            [
                                "number" => $data,
                            ],
                        );
                        break;

                    case "tvm.Slice":
                        $result[] = new DynamicTLObject(
                            "tvm.Slice",
                            [
                                "bytes" => $data,
                            ]
                        );
                        break;

                    default:
                        throw new \InvalidArgumentException(
                            "Unsupported stack entry: " . $type . "; index: " . $idx
                        );
                }
            } else {
                $givenMessage = is_array($entry) ? "associative array" : gettype($entry); // @phpstan-ignore-line

                throw new \InvalidArgumentException(
                    "Incorrect stack entry, list expected, " . $givenMessage . " given; index: " . $idx
                );
            }
        }

        return $result;
    }
}
