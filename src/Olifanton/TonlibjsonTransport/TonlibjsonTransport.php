<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Brick\Math\BigNumber;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\AddressState;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ResponseStack;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\Ton\Transport;
use Olifanton\Ton\Transports\Toncenter\Responses\QueryFees;
use Olifanton\TonlibjsonTransport\Async\Exceptions\FutureException;
use Olifanton\TonlibjsonTransport\Async\Executor;
use Olifanton\TonlibjsonTransport\Async\Future;
use Olifanton\TonlibjsonTransport\Async\FutureResolver;
use Olifanton\TonlibjsonTransport\Async\Loop;
use Olifanton\TonlibjsonTransport\Cache\SmcIdCache;
use Olifanton\TonlibjsonTransport\Exceptions\LiteServerError;
use Olifanton\TonlibjsonTransport\Exceptions\TonlibjsonTransportException;
use Olifanton\TonlibjsonTransport\TL\DynamicTLObject;
use Olifanton\TonlibjsonTransport\TL\Smc\MethodIdName;
use Olifanton\TonlibjsonTransport\TL\Smc\RunGetMethod;
use Olifanton\TonlibjsonTransport\TL\TLObject;
use Olifanton\TonlibjsonTransport\Tonlibjson\Client;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Olifanton\TypedArrays\Uint8Array;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TonlibjsonTransport implements Transport, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private int $timeout = 60;

    private bool $isInitialized = false;

    private ?Client $client = null;

    private ?Loop $loop = null;

    /**
     * @var array<string, array>
     */
    private array $results = [];

    private bool $isTerminating = false;

    public function __construct(
        private readonly TonlibInstance $tonlib,
        private readonly Executor $executor,
        private readonly string $config,
        private readonly string $keyStoreTypeDirectory,
    ) {}

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function runGetMethod(Contract|Address $contract, string $method, array $stack = []): ResponseStack
    {
        try {
            $address = $contract instanceof Contract ? $contract->getAddress() : $contract;
        } catch (ContractException $e) {
            throw new TransportException(
                "Contract address error: " . $e->getMessage(),
                0,
                $e,
            );
        }

        try {
            $smcId = SmcIdCache::ensure($address, $this);
        } catch (LiteServerError|TonlibjsonTransportException $e) {
            throw new TransportException(
                "Contract loading error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        try {
            $sStack = GetterStackSerializer::serialize($stack);
        } catch (\Throwable $e) {
            throw new TransportException(
                "Stack serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        try {
            $executionResult = $this->execute(new RunGetMethod(
                $smcId,
                new MethodIdName($method),
                $sStack,
            ));
        } catch (LiteServerError|TonlibjsonTransportException $e) {
            throw new TransportException(
                "Execution exception: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        $exitCode = $executionResult["exit_code"];

        if (!in_array($exitCode, [0, 1], true)) {
            throw new TransportException(
                "Non-zero exit code, code: " . $exitCode,
                $exitCode,
            );
        }

        try {
            return \Olifanton\TonlibjsonTransport\ResponseStack::parse($executionResult["stack"]);
        } catch (ResponseStackParsingException $e) {
            throw new TransportException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function send(Uint8Array|string|Cell $boc): void
    {
        // TODO: Implement send() method.
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function sendMessage(ExternalMessage $message, Uint8Array $secretKey): void
    {
        // TODO: Implement sendMessage() method.
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function estimateFee(Address $address,
                                string|Cell $body,
                                string|Cell|null $initCode = null,
                                string|Cell|null $initData = null): BigNumber
    {
        try {
            $createQuery = new DynamicTLObject(
                'raw.createQuery',
                [
                    "body" => is_string($body)
                        ? $body
                        : Bytes::bytesToBase64($body->toBoc(false)),
                    "init_code" => is_string($initCode) || is_null($initCode)
                        ? (string)$initCode
                        : Bytes::bytesToBase64($initCode->toBoc(false)),
                    "init_data" => is_string($initData) || is_null($initData)
                        ? (string)$initData
                        : Bytes::bytesToBase64($initData->toBoc(false)),
                    "destination" => [
                        "account_address" => $address->toString(),
                    ],
                ]
            );
        } catch (\Throwable $e) {
            throw new TonlibjsonTransportException(
                "Query creation error: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $queryInfo = $this->execute($createQuery);
        $id = $queryInfo['id'] ?? null;

        if (!$id) {
            throw new TonlibjsonTransportException("Query info error");
        }

        try {
            $fees = Hydrator::extract(QueryFees::class, $this->execute(new DynamicTLObject(
                "query.estimateFees",
                [
                    "id" => $id,
                    "ignore_chksig" => true,
                ]
            )));
        } catch (MarshallingException $e) {
            throw new TonlibjsonTransportException("Response deserialization error: " . $e->getMessage(), $e->getCode(), $e);
        }

        return $fees->sourceFees->sum();
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function getConfigParam(int $configParamId): Cell
    {
        // TODO: Implement getConfigParam() method.
    }

    /**
     * @inheritDoc
     * @throws TransportException
     */
    public function getState(Address $address): AddressState
    {
        // TODO: Implement getState() method.
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @throws TonlibjsonTransportException
     * @throws LiteServerError
     */
    public function execute(TLObject $object): ?array
    {
        try {
            $this->initialize();

            return $this
                ->executeInternal($object->jsonSerialize())
                ->await();
        } catch (TonlibjsonTransportException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TonlibjsonTransportException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function close(): void
    {
        $this->isTerminating = true;
    }

    /**
     * @return void
     * @throws TonlibjsonTransportException
     * @throws \Throwable
     */
    private function initialize(): void
    {
        if ($this->isInitialized) {
            return;
        }

        $this
            ->logger
            ?->debug("[TonlibjsonTransport] Start tonlibjson initialization");
        $this->client = $this->tonlib->create();
        $this->loop = $this->executor->ensureLoop();
        $extraId = $this->createExtraId();
        $this->executeInternal([
            "@type" => "init",
            "options" => [
                "@type" => "options",
                "config" => [
                    "@type" => "config",
                    "config" => $this->config,
                    "use_callbacks_for_network" => false,
                    "blockchain_name" => "",
                    "ignore_cache" => false,
                ],
                "keystore_type" => [
                    "@type" => "keyStoreTypeDirectory",
                    "directory" => $this->keyStoreTypeDirectory,
                ],
            ],
        ], $extraId, false);
        $this->loop->onTick(function () {
            if (!$this->isTerminating) {
                $excludedTypes = [
                    "updateSyncState"
                ];
                $result = $this
                    ->tonlib
                    ->receive(
                        $this->client,
                        $this->timeout,
                    );

                if ($result) {
                    $this
                        ->logger
                        ?->debug("[TonlibjsonTransport] Result received in tick callback: " . $result);
                    [$type, $id, $response] = $this->parseResponse($result);

                    if ($type && $id && $response) {
                        if (!in_array($type, $excludedTypes)) {
                            $this->results[$id] = $response;
                        }
                    }
                }
            } else {
                $this->loop->stop();
                $this->tonlib->destroy($this->client);
                $this->isInitialized = false;
            }
        });

        try {
            $future = $this->executor->createFuture(
                function (FutureResolver $resolver) use ($extraId) {
                    if ($this->popResponse($extraId)) {
                        $resolver->resolve(true);
                    }
                },
                $this->timeout,
            );
            $this->loop->run();
            $this->isInitialized = true;

            $future->await();
            $this
                ->logger
                ?->debug("[TonlibjsonTransport] Tonlibjson initialized");
        } catch (\Throwable $e) {
            throw new TonlibjsonTransportException(
                "Future error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @throws TonlibjsonTransportException
     */
    private function executeInternal(array $request, ?string $extraId = null, bool $future = true): ?Future
    {
        try {
            $extraId = $extraId ?? $this->createExtraId();
            $request["@extra"] = $extraId;
            $this
                ->logger
                ?->debug(sprintf(
                    "[TonlibjsonTransport] Start execution, @type: %s, @extra: %s",
                    $request["@type"] ?? "unknown",
                    $extraId,
                ));
            $this
                ->tonlib
                ->send(
                    $this->client,
                    json_encode($request, JSON_THROW_ON_ERROR) . "\0",
                );

            return $future ? $this->executor->createFuture(
                function (FutureResolver $resolver) use ($extraId) {
                    if ($response = $this->popResponse($extraId)) {
                        $resolver->resolve($this->tryExtractError($response) ?? $response);
                    }
                },
                $this->timeout,
            ) : null;
        } catch (\JsonException $e) {
            $errorMessage = sprintf(
                "Request serialization error: %s",
                $e->getMessage(),
            );

            $this
                ->logger
                ?->error(
                    "[TonlibjsonTransport] " . $errorMessage,
                    [
                        "exception" => $e,
                        "@type" => $request["@type"] ?? null,
                    ],
                );

            throw new TonlibjsonTransportException(
                $errorMessage,
                $e->getCode(),
                $e,
            );
        } catch (FutureException $e) {
            $errorMessage = sprintf(
                "Future creation error: %s",
                $e->getMessage(),
            );

            $this
                ->logger
                ?->error(
                    "[TonlibjsonTransport] " . $errorMessage,
                    [
                        "exception" => $e,
                        "@type" => $request["@type"] ?? null,
                    ],
                );

            throw new TonlibjsonTransportException(
                $errorMessage,
                $e->getCode(),
                $e,
            );
        }
    }

    private function createExtraId(): string
    {
        return sprintf(
            "%s:%s:%s",
            time() + $this->timeout,
            0,
            mt_rand(),
        );
    }

    /**
     * @return array{string|null, string|null, array|null}
     */
    private function parseResponse(string $result): array
    {
        try {
            $response = json_decode($result, true, flags: JSON_THROW_ON_ERROR);

            if (is_array($response) && isset($response["@extra"]) && $response["@type"]) {
                return [$response["@type"], $response["@extra"], $response];
            }
        } catch (\JsonException $e) {
            $this
                ->logger
                ?->warning(
                    "[TonlibjsonTransport] Response parsing error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                    ]
                );
        }

        return [null, null, null];
    }

    private function popResponse(string $extraId): ?array
    {
        if (isset($this->results[$extraId])) {
            $response = $this->results[$extraId];
            unset($this->results[$extraId]);

            return $response;
        }

        return null;
    }

    private function tryExtractError(array $response): ?LiteServerError
    {
        if (isset($response["@type"]) && $response["@type"] === "error") {
            return new LiteServerError(
                $response["message"] ?? "Unknown lite server error",
                    $response["code"] ?? 0,
                $response,
            );
        }

        return null;
    }
}
