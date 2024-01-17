<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\Interop\Address;
use Olifanton\Interop\KeyPair;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;
use Olifanton\Ton\Contracts\Wallets\Wallet;
use Olifanton\Ton\Contracts\Wallets\WalletOptions;

class Environment
{
    /**
     * @param class-string<Wallet> $deploymentWallet
     */
    public function __construct(
        public readonly string $deploymentWallet,
        public readonly KeyPair $deploymentWalletKP,
        public readonly Address $deploymentWalletAddr,
        public readonly array $config,
    ) {}

    public function getDeployWallet(): Wallet
    {
        $options = match ($this->deploymentWallet) {
            WalletV3R2::class,WalletV3R1::class => new WalletV3Options(
                publicKey: $this->deploymentWalletKP->publicKey,
            ),
            default => $this->createWalletOptions(),
        };

        return new $this->deploymentWallet($options);
    }

    protected function createWalletOptions(): WalletOptions
    {
        throw new \RuntimeException("Not implemented, Implement your own Environment");
    }
}
