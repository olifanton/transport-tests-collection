<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;

class HttpClient
{
    public static function discovery(): HttpMethodsClient
    {
        return new HttpMethodsClient(
            \Http\Discovery\Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
    }
}
