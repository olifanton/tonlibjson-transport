<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

use Olifanton\TonlibjsonTransport\Exceptions\BuilderException;
use Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException;
use Olifanton\TonlibjsonTransport\Helpers\HttpClientFactory;
use Olifanton\TonlibjsonTransport\Models\LiteServer;
use Olifanton\TonlibjsonTransport\Pool\Factories\BlockingPoolFactory;
use Olifanton\TonlibjsonTransport\Pool\RandomSelector;
use Olifanton\TonlibjsonTransport\Pool\Selector;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TonlibjsonTransportBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private string $configUrl;

    private ?Locator $locator = null;

    private ?string $libDirectory = null;

    /**
     * @var LiteServer[]|null
     */
    private ?array $liteServers = null;

    private ?Selector $selector = null;

    private ?LiteServerRepository $liteServerRepository = null;

    private ?ClientPoolFactory $clientPoolFactory = null;

    public function __construct(bool $isMainnet = true)
    {
        $this->configUrl = $isMainnet ? ConfigUrl::MAINNET->value : ConfigUrl::TESTNET->value;
    }

    public function setConfigUrl(ConfigUrl|string $configUrl): self
    {
        $this->configUrl = $configUrl instanceof ConfigUrl ? $configUrl->value : $configUrl;

        return $this;
    }

    public function setLiteServerRepository(LiteServerRepository $liteServerRepository): self
    {
        $this->liteServerRepository = $liteServerRepository;

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

    public function setSelector(Selector $selector): self
    {
        $this->selector = $selector;

        return $this;
    }

    public function setClientPoolFactory(ClientPoolFactory $clientPoolFactory): self
    {
        $this->clientPoolFactory = $clientPoolFactory;

        return $this;
    }

    /**
     * @throws BuilderException
     */
    public function build(): TonlibjsonTransport
    {
        $tonlib = $this->createTonlib();
        $instance = new TonlibjsonTransport(
            $this->pool ?? $this->createPool($tonlib),
            $this->selector ?? $this->createSelector(),
            $this->getLiteServers(),
        );

        if ($this->logger) {
            $instance->setLogger($this->logger);
        }

        return $instance;
    }

    protected function createPool(TonlibInstance $tonlib): ClientPool
    {
        $factory = $this->clientPoolFactory ?? new BlockingPoolFactory();
        $instance = $factory->getPool($tonlib);

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

    protected function createSelector(): Selector
    {
        return new RandomSelector();
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
     * @return LiteServer[]
     * @throws BuilderException
     */
    protected function getLiteServers(): array
    {
        if (!$this->liteServers) {
            try {
                return $this->getLiteServerRepository()->getList();
            } catch (Exceptions\LiteServerFetchingException $e) {
                throw new BuilderException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $this->liteServers;
    }

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
