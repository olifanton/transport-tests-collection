<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Cases\Deployer;

use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4Options;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2;
use Olifanton\Ton\DeployOptions;
use Olifanton\Ton\Helpers\KeyPair;
use Olifanton\Ton\Transport;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\TestCase;

#[AsCase("deployer:wallet")]
final class DeployWallet extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function run(Transport $transport): void
    {
        $deployer = new \Olifanton\Ton\Deployer($transport);
        $deployer->setLogger($this->logger);
        $this->logger->debug("Deployer created");

        $deployWallet = $this->getDeployWallet();

        $newWallet = new WalletV4R2(
            new WalletV4Options(
                publicKey: KeyPair::random()->publicKey,
            ),
        );

        $this
            ->logger
            ->debug(sprintf(
                "New wallet with random Key Pair created, address: %s",
                $newWallet->getAddress()->toString(
                    isUserFriendly: true,
                    isUrlSafe: true,
                    isBounceable: false,
                )
            ));

        $deployOptions = new DeployOptions(
            $deployWallet,
            $this->getSecretKey(),
            Units::toNano("0.05"),
        );

        $deployer->deploy($deployOptions, $newWallet);
        $this->assertActiveContract($transport, $newWallet->getAddress(), "Wallet activated");

        $this->logger->debug(
            "Verifier url: https://verifier.ton.org/" . $newWallet->getAddress()->toString(true, true, false) . "?testnet="
        );
    }
}
