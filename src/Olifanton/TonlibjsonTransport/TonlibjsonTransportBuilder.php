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

    private VerbosityLevel $verbosityLevel = VerbosityLevel::ERROR;

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

    /**
     * @throws BuilderException
     */
    public function build(): TonlibjsonTransport
    {
        $tonlib = $this->createTonlib();
        $tonlib->setVerbosityLevel($this->verbosityLevel);
        $instance = new TonlibjsonTransport(
            $tonlib,
            $this->executor,
            // FIXME
        );

        if ($this->logger) {
            $instance->setLogger($this->logger);
        }

        return $instance;
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


    /**
     * @deprecated
     */
    protected function getLiteServerRepository(): LiteServerRepository
    {
        if (!$this->liteServerRepository) {
            $liteServerRepository = new HttpLiteServerRepository(
                HttpClientFactory::discovered(),
                $this->configUrl,
            );

            if ($this->logger) {
                $liteServerRepository->setLogger($this->logger);
            }

            return $liteServerRepository;
        }

        return $this->liteServerRepository;
    }
}
