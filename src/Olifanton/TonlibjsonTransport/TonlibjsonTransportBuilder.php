<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\TonlibjsonTransport\Exceptions\BuilderException;
use Olifanton\TonlibjsonTransport\Models\LiteServer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TonlibjsonTransportBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private string $configUrl;

    private Locator $locator;

    /**
     * @var LiteServer[]|null
     */
    private ?array $liteServers = null;

    public function __construct(
        private readonly HttpMethodsClientInterface $httpClient,
        private readonly string $libDirectory,
    )
    {
        $this->configUrl = ConfigUrl::MAINNET->value;
        $this->locator = new Locator($this->libDirectory);
    }

    public function setConfigUrl(ConfigUrl|string $configUrl): self
    {
        $this->configUrl = $configUrl instanceof ConfigUrl ? $configUrl->value : $configUrl;

        return $this;
    }

    /**
     * @param LiteServer[] $liteServers
     */
    public function setLiteServers(array $liteServers): self
    {
        $this->liteServers = $liteServers;

        return $this;
    }

    /**
     * @throws BuilderException
     */
    public function build(): TonlibjsonTransport
    {
        if (!$this->liteServers) {
            $this->liteServers = $this->receiveLiteServers();
        }

        // FIXME
    }

    /**
     * @return LiteServer[]
     * @throws \Http\Client\Exception
     * @throws \JsonException
     * @throws \Olifanton\Ton\Marshalling\Exceptions\MarshallingException
     */
    protected function receiveLiteServers(): array
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

            throw new \RuntimeException("Bad config request");
        }

        return array_map(static fn(array $ls) => Hydrator::extract(LiteServer::class, $ls), $config["liteservers"]);
    }
}
