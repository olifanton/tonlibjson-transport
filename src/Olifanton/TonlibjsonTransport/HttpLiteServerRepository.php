<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\TonlibjsonTransport\Exceptions\LiteServerFetchingException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class HttpLiteServerRepository implements LiteServerRepository, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly HttpMethodsClientInterface $httpClient,
        private readonly string $configUrl,
    ) {}

    /**
     * @inheritDoc
     */
    public function getList(): array
    {
        try {
            return $this->fetchLiteServers();
        } catch (\JsonException|\Http\Client\Exception|MarshallingException $e) {
            $error = sprintf(
                "Liteservers fetching error: %s",
                $e->getMessage(),
            );
            $this
                ->logger
                ?->error(
                    $error,
                    [
                        "exception" => $e,
                    ]
                );

            throw new LiteServerFetchingException(
                $error,
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @return LiteServer[]
     * @throws \Http\Client\Exception
     * @throws \JsonException
     * @throws MarshallingException
     */
    protected function fetchLiteServers(): array
    {
        $configJson = $this->httpClient->get($this->configUrl)->getBody()->getContents();
        $config = json_decode($configJson, true, flags: JSON_THROW_ON_ERROR);

        if (!isset($config["liteservers"])) {
            $this
                ->logger
                ?->error(
                    "Bad config response",
                    [
                        "response" => $configJson,
                    ]
                );

            throw new \RuntimeException("Bad config response");
        }

        return array_map(
            static fn(array $ls) => Hydrator::extract(LiteServer::class, $ls),
            $config["liteservers"],
        );
    }
}
