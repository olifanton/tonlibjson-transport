<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Pool;

use Olifanton\TonlibjsonTransport\Tonlibjson\Client;
use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;
use Psr\Log\LoggerInterface;

/**
 * @property-read TonlibInstance $tonlib
 * @property-read LoggerInterface|null $logger
 */
trait CommonPool
{
    /**
     * @var array<string, Client>
     */
    protected array $clientMap = [];

    /**
     * @var array<string>
     */
    protected array $borrowed = [];

    protected bool $isClosed = false;

    public function return(Client $client): void
    {
        if ($this->isClosed) {
            throw new \RuntimeException("Pool already closed");
        }

        $key = array_search($client->id, $this->borrowed);

        if ($key !== false) {
            unset($this->borrowed[$key]);
            $this
                ->logger
                ?->debug("Client returned, id " . $client->id);
        }
    }

    public function close(): void
    {
        if (!$this->isClosed) {
            foreach ($this->clientMap as $client) {
                $this->tonlib->destroy($client);
            }
        }

        $this->isClosed = true;
        $this
            ->logger
            ?->debug("Pool closed");
    }

    protected function createClient(): Client
    {
        $client = $this->tonlib->create();
        $this->clientMap[$client->id] = $client;

        return $client;
    }

    protected function isHaveFree(int $maxSize): bool
    {
        $createdCount = count($this->clientMap);

        if (count($this->borrowed) < $createdCount) {
            return true;
        }

        return $createdCount < $maxSize;
    }

    protected function getFreeClient(int $maxPoolSize): Client
    {
        $clientCount = count($this->clientMap);
        $borrowedCount = count($this->borrowed);

        if ($clientCount === $borrowedCount) {
            $freeClient = $this->createClient();
        } else {
            $freeClients = array_values(array_filter(
                $this->clientMap,
                fn(Client $client) => !in_array($client->id, $this->borrowed),
            ));

            $freeClient = $freeClients[0];
        }

        $this->borrowed[] = $freeClient->id;

        return $freeClient;
    }
}
