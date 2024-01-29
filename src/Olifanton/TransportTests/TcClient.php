<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;

class TcClient
{
    protected static ?ToncenterHttpV2Client $client = null;

    public static function getInstance(): ToncenterHttpV2Client
    {
        if (!self::$client) {
            self::$client = new ToncenterHttpV2Client(
                HttpClient::discovery(),
                new ClientOptions(
                    baseUri: "https://testnet.toncenter.com/api/v2",
                    apiKey: Environment::getInstance()->config["toncenter_api_key"],
                ),
            );
        }

        return self::$client;
    }
}
