<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\Interop\Address;
use Olifanton\Interop\Bytes;
use Olifanton\Interop\KeyPair;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;
use Olifanton\Ton\Contracts\Wallets\Wallet;
use Olifanton\Ton\Contracts\Wallets\WalletOptions;
use Olifanton\TypedArrays\Uint8Array;

class Environment
{
    protected static Environment|null $instance = null;

    protected ?Runtime $runtime = null;

    /**
     * @param class-string<Wallet> $deploymentWallet
     * @param array{deployment_wallet: array{secret_key: string, class: class-string, address: string},env: class-string,runtime: class-string|null,toncenter_api_key: string,cases: array<string, class-string>} $config
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

    public static function getInstance(): Environment
    {
        if (self::$instance === null) {
            $config = Configuration::read();
            /** @var class-string<Environment> $envClass */
            $envClass = $config["env"];

            self::$instance = new $envClass(
                $config["deployment_wallet"]["class"],
                KeyPair::fromSecretKey(Bytes::base64ToBytes($config["deployment_wallet"]["secret_key"])),
                new Address($config["deployment_wallet"]["address"]),
                $config,
            );
        }

        return self::$instance;
    }

    public function getRuntime(): Runtime
    {
        if (!$this->runtime) {
            $runtimeClass = $this->config["runtime"];

            if ($runtimeClass === null) {
                throw new \InvalidArgumentException("Runtime implementation is not specified");
            }

            $this->runtime = call_user_func([$runtimeClass, "create"]);
        }

        return $this->runtime;
    }

    public function getSecretKey(): Uint8Array
    {
        return $this->deploymentWalletKP->secretKey;
    }

    protected function createWalletOptions(): WalletOptions
    {
        throw new \RuntimeException("Not implemented, Implement your own Environment");
    }
}
