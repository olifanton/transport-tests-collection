<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Cases\Jettons;

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonMinterOptions;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Contracts\Jetton\JettonWalletOptions;
use Olifanton\Ton\Contracts\Jetton\MintOptions;
use Olifanton\Ton\Contracts\Jetton\TransferJettonOptions;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;
use Olifanton\Ton\Deployer;
use Olifanton\Ton\DeployOptions;
use Olifanton\Ton\Helpers\KeyPair;
use Olifanton\Ton\SendMode;
use Olifanton\Ton\Transport;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\TestCase;

#[AsCase("jettons:mint-transfer")]
class Mint extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function run(Transport $transport): void
    {
        $adminWalletPk = KeyPair::random();
        $adminWallet = new WalletV3R2(
            new WalletV3Options(
                publicKey: $adminWalletPk->publicKey,
            ),
        );

        // ========= Subcase 1: Create minter and mint

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
                Units::toNano(0.3),
            ),
            $adminWallet,
        );
        $this->assertActiveContract(
            $transport,
            $adminWallet->getAddress(),
            "Admin wallet contract",
        );

        // Mint jettons
        $transfer = $adminWallet->createTransferMessage(
            [
                new Transfer(
                    dest: $minter->getAddress(),
                    amount: Units::toNano(0.05),
                    payload: JettonMinter::createMintBody(new MintOptions(
                        jettonAmount: Units::toNano("1000000"),
                        destination: $adminWallet->getAddress(),
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

        $jettonWalletAddress = $minter->getJettonWalletAddress($transport, $adminWallet->getAddress());
        $this
            ->logger
            ->debug(sprintf(
                "Jetton wallet address: %s",
                $jettonWalletAddress->toString(true, true, false),
            ));
        $this->assertActiveContract($transport, $jettonWalletAddress, "Jetton address");
        sleep(1);

        // ========= Subcase 2: transfer to other wallet

        $otherWalletAddress = new Address("EQBfiu4Ta8S1n9cgpnZDKqNlMqiFn2K7GGLEfc-h-GmghL7s");

        $jettonWallet = new JettonWallet(
            new JettonWalletOptions(
                address: $jettonWalletAddress,
            ),
        );
        $transfer = $adminWallet->createTransferMessage(
            [
                new Transfer(
                    dest: $jettonWalletAddress,
                    amount: Units::toNano(0.1),
                    payload: $jettonWallet->createTransferBody(new TransferJettonOptions(
                        jettonAmount: Units::toNano("123456"),
                        toAddress: $otherWalletAddress,
                        responseAddress: $jettonWalletAddress,
                        queryId: random_int(0, PHP_INT_MAX),
                    )),
                    sendMode: SendMode::PAY_GAS_SEPARATELY->combine(SendMode::IGNORE_ERRORS),
                    bounce: false,
                )
            ],
            new TransferOptions(
                seqno: (int)$adminWallet->seqno($transport),
            )
        );
        $transport->sendMessage($transfer, $adminWalletPk->secretKey);
        $this
            ->logger
            ->debug("Jettons transfer sent");
        $this->expectTransaction(
            $jettonWalletAddress,
            $minter->getJettonWalletAddress(
                $transport,
                $otherWalletAddress,
            ),
            "Jettons transaction to other wallet",
        );
    }
}
