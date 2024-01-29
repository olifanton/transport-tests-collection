<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\Interop\Address;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\ContractAwaiter;
use Olifanton\Ton\Contracts\Wallets\Wallet;
use Olifanton\Ton\Exceptions\AwaiterMaxTimeException;
use Olifanton\Ton\Transport;
use Olifanton\TypedArrays\Uint8Array;
use Psr\Log\LoggerInterface;

abstract class TestCase
{
    public function __construct(
        protected readonly Environment $environment,
        protected readonly Context $context,
        protected readonly LoggerInterface $logger,
    ) {}

    abstract public function run(Transport $transport): void;

    public function getSecretKey(): Uint8Array
    {
        return $this->environment->getSecretKey();
    }

    public function getDeployWallet(): Wallet
    {
        return $this->environment->getDeployWallet();
    }

    public function assert(bool $condition, string $message): void
    {
        $this->context->assert($condition, $message);
    }

    public function assertActiveContract(Transport $transport, Address $address, string $message): void
    {
        $awaiter = new ContractAwaiter($transport);

        try {
            $awaiter->waitForActive($address);
            $isActiveContract = true;
        } catch (AwaiterMaxTimeException $e) {
            $isActiveContract = false;
        }

        $this->context->assert($isActiveContract, $message);
    }

    public function assertAddress(Address $expected, ?Address $actual, ?string $message = null): void
    {
        if (!$actual) {
            $this
                ->context
                ->assert(
                    false,
                    sprintf(
                        "%sFailed asserting address, expected: %s, actual: null",
                        $message ? ($message . ": ") : "",
                        $expected->toString(
                            isUserFriendly: true, isUrlSafe: true, isBounceable: false, isTestOnly: false,
                        ),
                    ),
                );
            return;
        }

        $this
            ->context
            ->assert(
                Bytes::compareBytes(
                    $expected->getHashPart(),
                    $actual->getHashPart(),
                ),
                sprintf(
                    "%sFailed asserting address, expected: %s, actual: %s",
                    $message ? ($message . ": ") : "",
                    $expected->toString(
                        isUserFriendly: true, isUrlSafe: true, isBounceable: false, isTestOnly: false,
                    ),
                    $actual->toString(
                        isUserFriendly: true, isUrlSafe: true, isBounceable: false, isTestOnly: false,
                    ),
                ),
            );
    }
}
