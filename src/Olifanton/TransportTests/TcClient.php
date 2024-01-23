<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;

class TcClient
{
    protected static ?ToncenterHttpV2Client $client = null;

    public static function getInstance(): ToncenterHttpV2Client
    {
        if (!self::$client) {
            self::$client = new ToncenterHttpV2Client(
                new HttpMethodsClient(
                    \Http\Discovery\Psr18ClientDiscovery::find(),
                    Psr17FactoryDiscovery::findRequestFactory(),
                    Psr17FactoryDiscovery::findStreamFactory(),
                ),
                new ClientOptions(
                    baseUri: "https://testnet.toncenter.com/api/v2",
                    apiKey: Environment::getInstance()->config["toncenter_api_key"],
                ),
            );
        }

        return self::$client;
    }
}
