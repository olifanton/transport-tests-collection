<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Cases\Jettons;

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonMinterOptions;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Contracts\Jetton\MintOptions;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;
use Olifanton\Ton\Deployer;
use Olifanton\Ton\DeployOptions;
use Olifanton\Ton\Helpers\KeyPair;
use Olifanton\Ton\Transport;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\TestCase;

#[AsCase("jettons:mint")]
class Mint extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function run(Transport $transport): void
    {
        $jettonsReceiver = new Address("EQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAM9c");

        $adminWalletPk = KeyPair::random();
        $adminWallet = new WalletV3R2(
            new WalletV3Options(
                publicKey: $adminWalletPk->publicKey,
            ),
        );

        // Minter instance
        $minter = new JettonMinter(
            new JettonMinterOptions(
                adminAddress: $adminWallet->getAddress(),
                jettonContentUrl: "https://api.npoint.io/253d1035fc682ef502a1",
                jettonWalletCode: JettonWallet::getDefaultCode(),
            ),
        );

        $this
            ->logger
            ->debug(sprintf(
                "Admin wallet and minter created, admin wallet addr: %s",
                $adminWallet->getAddress()->toString(true, true, false),
            ));

        $deployer = new Deployer($transport);
        $deployer->setLogger($this->logger);

        // Deploy minter
        $deployer->deploy(
            new DeployOptions(
                $this->getDeployWallet(),
                $this->getSecretKey(),
                Units::toNano(0.1),
            ),
            $minter,
        );
        $this->assertActiveContract(
            $transport,
            $minter->getAddress(),
            "Minter contract",
        );

        // Deploy admin wallet
        $deployer->deploy(
            new DeployOptions(
                $this->getDeployWallet(),
                $this->getSecretKey(),
                Units::toNano(0.1),
            ),
            $adminWallet,
        );
        $this->assertActiveContract(
            $transport,
            $adminWallet->getAddress(),
            "Admin wallet contract",
        );

        // Transfer tokens
        $transfer = $adminWallet->createTransferMessage(
            [
                new Transfer(
                    dest: $minter->getAddress(),
                    amount: Units::toNano(0.05),
                    payload: JettonMinter::createMintBody(new MintOptions(
                        jettonAmount: Units::toNano("1000000"),
                        destination: $jettonsReceiver,
                        amount: Units::toNano(0.05),
                    )),
                    bounce: false,
                ),
            ],
            new TransferOptions(
                seqno: (int)$adminWallet->seqno($transport),
            ),
        );
        $transport->sendMessage($transfer, $adminWalletPk->secretKey);
        $this
            ->logger
            ->debug("Mint transfer sent");

        $jettonWalletAddress = $minter->getJettonWalletAddress($transport, $jettonsReceiver);
        $this
            ->logger
            ->debug(sprintf(
                "Jetton wallet address: %s",
                $jettonWalletAddress->toString(true, true, false),
            ));
        $this->assertActiveContract($transport, $jettonWalletAddress, "Jetton address");
    }
}
