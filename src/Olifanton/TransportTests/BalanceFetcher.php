<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Brick\Math\BigInteger;

class BalanceFetcher
{
    public static function getBalance(): BigInteger
    {
        return TcClient::getInstance()->getAddressBalance(
            Environment::getInstance()->deploymentWalletAddr,
        );
    }
}
