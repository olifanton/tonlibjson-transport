<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\TonlibjsonTransport\Exceptions\BuilderException;
use Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException;
use Olifanton\TonlibjsonTransport\Models\LiteServer;
use Olifanton\TonlibjsonTransport\Pool\RandomSelector;
use Olifanton\TonlibjsonTransport\Pool\Selector;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TonlibjsonTransportBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private string $configUrl;

    private ?LocatorInterface $locator = null;

    private ?string $libDirectory = null;

    /**
     * @var LiteServer[]|null
     */
    private ?array $liteServers = null;

    private ?Selector $poolSelector = null;

    public function __construct(
        private readonly HttpMethodsClientInterface $httpClient, // FIXME: Remove this dependency, optional `LiteServerRepository` needed
    )
    {
        $this->configUrl = ConfigUrl::MAINNET->value;
    }

    public function setConfigUrl(ConfigUrl|string $configUrl): self // FIXME: Move to HttpConfigRepository
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

    public function setLocator(LocatorInterface $locator): self
    {
        $this->locator = $locator;

        return $this;
    }

    public function setLibDirectory(string $libDirectory): self
    {
        $this->libDirectory = $libDirectory;

        return $this;
    }

    public function setPoolSelector(Selector $poolSelector): self
    {
        $this->poolSelector = $poolSelector;

        return $this;
    }

    /**
     * @throws BuilderException
     */
    public function build(): TonlibjsonTransport
    {
        if (!$this->locator) {
            if (!$this->libDirectory) {
                throw new BuilderException(
                    "For automatic Locator, you need to specify `libDirectory`",
                );
            }

            $this->locator = new Locator($this->libDirectory);
        }

        if (!$this->liteServers) {
            try {
                $this->liteServers = $this->fetchLiteServers();
            } catch (\JsonException|\Http\Client\Exception|MarshallingException $e) {
                throw new BuilderException(
                    sprintf(
                        "Liteservers fetching error: %s",
                        $e->getMessage(),
                    ),
                    $e->getCode(),
                    $e,
                );
            }
        }

        try {
            $tonlib = new TonlibInstance($this->locator->locatePath());
        } catch (LibraryLocationException $e) {
            throw new BuilderException(
                sprintf(
                    "Locator error: %s",
                    $e->getMessage(),
                ),
                $e->getCode(),
                $e,
            );
        }

        $pool = new ClientPool(
            $tonlib,
            $this->liteServers,
            $this->poolSelector ?? new RandomSelector(),
        );

        if ($this->logger) {
            $pool->setLogger($this->logger);
        }

        $instance = new TonlibjsonTransport($pool);

        if ($this->logger) {
            $instance->setLogger($this->logger);
        }

        return $instance;
    }

    /**
     * @return LiteServer[]
     * @throws \Http\Client\Exception
     * @throws \JsonException
     * @throws MarshallingException
     */
    protected function fetchLiteServers(): array // FIXME Move to HttpConfigRepository
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
