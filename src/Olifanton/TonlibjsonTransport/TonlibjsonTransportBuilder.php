<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Async\Executor;
use Olifanton\TonlibjsonTransport\Exceptions\BuilderException;
use Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException;
use Olifanton\TonlibjsonTransport\Helpers\HttpClientFactory;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Psr\Log\LoggerInterface;

class TonlibjsonTransportBuilder
{
    private string $configUrl;

    private ?LoggerInterface $logger = null;

    private ?Locator $locator = null;

    private ?string $libDirectory = null;

    /**
     * @var LiteServer[]|null
     */
    private ?array $liteServers = null;

    private ?string $config = null;

    private VerbosityLevel $verbosityLevel = VerbosityLevel::ERROR;

    private ?string $keyStoreTypeDirectory = null;

    public function __construct(
        private readonly Executor $executor,
        bool $isMainnet = true,
    )
    {
        $this->configUrl = $isMainnet ? ConfigUrl::MAINNET->value : ConfigUrl::TESTNET->value;
    }

    public function setVerbosityLevel(VerbosityLevel $verbosityLevel): self
    {
        $this->verbosityLevel = $verbosityLevel;

        return $this;
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
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
        foreach ($liteServers as $ls) {
            if (!$ls instanceof LiteServer) {
                throw new \InvalidArgumentException();
            }
        }

        $this->liteServers = $liteServers;

        return $this;
    }

    public function setLocator(Locator $locator): self
    {
        $this->locator = $locator;

        return $this;
    }

    public function setLibDirectory(string $libDirectory): self
    {
        $this->libDirectory = $libDirectory;

        return $this;
    }

    public function setKeyStoreTypeDirectory(string $keyStoreTypeDirectory): self
    {
        $this->keyStoreTypeDirectory = $keyStoreTypeDirectory;

        return $this;
    }

    public function setConfig(string $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @throws BuilderException
     */
    public function build(): TonlibjsonTransport
    {
        try {
            $tonlib = $this->createTonlib();
            $tonlib->setVerbosityLevel($this->verbosityLevel);
            $config = $this->getConfig();

            if (!empty($this->liteServers)) {
                $config = $this->replaceLiteServers($config, $this->liteServers);
            }

            $instance = new TonlibjsonTransport(
                $tonlib,
                $this->executor,
                $config,
                $this->keyStoreTypeDirectory ?? sys_get_temp_dir(),
            );

            if ($this->logger) {
                $instance->setLogger($this->logger);
            }

            return $instance;
        } catch (\Throwable $e) {
            throw new BuilderException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws BuilderException
     */
    protected function createTonlib(): TonlibInstance
    {
        try {
            return new TonlibInstance($this->getLocator()->locatePath());
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
    }

    /**
     * @throws BuilderException
     */
    protected function getLocator(): Locator
    {
        if (!$this->locator) {
            if (!$this->libDirectory) {
                throw new BuilderException(
                    "For automatic Locator, you need to specify `libDirectory`",
                );
            }

            return new GenericLocator($this->libDirectory);
        }

        return $this->locator;
    }

    protected function getConfig(): string
    {
        if ($this->config) {
            return $this->config;
        }

        return $this->downloadConfig($this->configUrl);
    }

    protected function downloadConfig(string $configUrl): string
    {
        $httpClient = HttpClientFactory::discovered();
        $response = $httpClient->get($configUrl);

        return $response->getBody()->getContents();
    }

    /**
     * @param LiteServer[] $liteServers
     * @throws \JsonException
     */
    protected final function replaceLiteServers(string $config, array $liteServers): string
    {
        $parsedConfig = json_decode($config, true, flags: JSON_THROW_ON_ERROR);
        $parsedConfig["liteservers"] = array_map(static fn (LiteServer $ls): array => $ls->toArray(), $liteServers);

        return json_encode($parsedConfig, JSON_THROW_ON_ERROR);
    }
}
