<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Helpers;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

class HttpClientFactory
{
    public static function discovered(): HttpMethodsClientInterface
    {
        return new HttpMethodsClient(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
    }
}
