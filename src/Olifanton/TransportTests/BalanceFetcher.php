<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Brick\Math\BigInteger;

class BalanceFetcher
{
    /**
     * @throws \Olifanton\Ton\Transports\Toncenter\Exceptions\ClientException
     * @throws \Olifanton\Ton\Transports\Toncenter\Exceptions\TimeoutException
     * @throws \Olifanton\Ton\Transports\Toncenter\Exceptions\ValidationException
     */
    public static function getBalance(): BigInteger
    {
        return TcClient::getInstance()->getAddressBalance(
            Environment::getInstance()->deploymentWalletAddr,
        );
    }
}
