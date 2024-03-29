<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Tonlibjson;

use FFI\Scalar\Type;
use Olifanton\TonlibjsonTransport\VerbosityLevel;

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

    public function create(): Client
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $clientId = hash("crc32", random_bytes(128));

        return new Client(
            $this->ffi->tonlib_client_json_create(),
            $clientId,
        );
    }

    public function destroy(Client $client): void
    {
        $this->ffi->tonlib_client_json_destroy($client->ptr);
    }

    public function send(Client $client, string $request): void
    {
        $this
            ->ffi
            ->tonlib_client_json_send(
                $client->ptr,
                Type::charArray(str_split($request)),
            );
    }

    public function execute(Client $client, string $request): ?string
    {
        $data = $this
            ->ffi
            ->tonlib_client_json_execute(
                $client->ptr,
                $request,
            );

        if ($data && is_string($data)) {
            return $data;
        }

        return null;
    }

    public function receive(Client $client, float $timeout): ?string
    {
        $data = $this
            ->ffi
            ->tonlib_client_json_receive(
                $client->ptr,
                $timeout,
            );

        if ($data && is_string($data)) {
            return $data;
        }

        return null;
    }

    public function setVerbosityLevel(VerbosityLevel $level): void
    {
        $this->ffi->tonlib_client_set_verbosity_level($level->value);
    }
}
