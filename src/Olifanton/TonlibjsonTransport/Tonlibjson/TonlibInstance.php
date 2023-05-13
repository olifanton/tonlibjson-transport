<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Tonlibjson;

use FFI\CData;
use FFI\Scalar\Type;

class TonlibInstance
{
    private \FFI $ffi;

    public function __construct(string $libPath)
    {
        $cDef = /** @lang C */<<<C
        extern void *tonlib_client_json_create();
        extern void tonlib_client_set_verbosity_level(int verbosity_level);
        extern void tonlib_client_json_send(void *client, const char *request);
        extern const char *tonlib_client_json_receive(void *client, double timeout);
        extern const char *tonlib_client_json_execute(void *client, const char *request);
        extern void tonlib_client_json_destroy(void *client);
        C;
        $this->ffi = \FFI::cdef($cDef, $libPath);
    }

    public function create(): CData
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ffi->tonlib_client_json_create();
    }

    public function destroy(CData $client): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->ffi->tonlib_client_json_destroy($client);
    }

    public function receive(CData $client, float $timeout): CData
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this
            ->ffi
            ->tonlib_client_json_receive(
                $client,
                Type::double($timeout),
            );
    }
}
