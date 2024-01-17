<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Cases\Deployer;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Units;
use Olifanton\Ton\ContractAwaiter;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\Ton\DeployOptions;
use Olifanton\Ton\Transport;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\TestCase;
use Olifanton\TransportTests\TestResult;

#[AsCase("deployer:custom-contract")]
class DeployCustomContract extends TestCase
{
    public function run(Transport $transport): TestResult
    {
        return $this->runtime->run(function () use ($transport) {
            try {
                $deployer = new \Olifanton\Ton\Deployer($transport);
                $deployer->setLogger($this->logger);
                $this->logger->debug("Deployer created");

                $deployWallet = $this->environment->getDeployWallet();
                $this->logger->debug(sprintf(
                    "Deploy wallet created, addr: %s",
                    $deployWallet->getAddress()->toString(true, true, false),
                ));

                $exampleContract = new class(new ContractOptions()) extends AbstractContract
                {
                    protected function createCode(): Cell
                    {
                        // Compiled BoC from Blueprint's simple counter contract (https://github.com/ton-community/blueprint/blob/main/src/templates/counter.contract.fc.template)
                        return Cell::oneFromBoc("b5ee9c7241010a010089000114ff00f4a413f4bcf2c80b01020162050202016e0403000db63ffe003f0850000db5473e003f08300202ce070600194f842f841c8cb1fcb1fc9ed5480201200908001d3b513434c7c07e1874c7c07e18b46000671b088831c02456f8007434c0cc1c6c244c383c0074c7f4cfcc4060841fa1d93beea6f4c7cc3e1080683e18bc00b80c2103fcbc208d7eb34a");
                    }

                    protected function createData(): Cell
                    {
                        $data = new Cell();
                        $bs = $data->bits;

                        $bs
                            ->writeUint(\Brick\Math\BigInteger::fromBase(bin2hex(random_bytes(4)), 16), 32) // ctx_id
                            ->writeUint(0, 32); // ctx_counter

                        return $data;
                    }

                    public static function getName(): string
                    {
                        return "example";
                    }
                };
                $this
                    ->logger
                    ->debug(sprintf(
                        "Example contract created, address: %s",
                        $exampleContract->getAddress()->toString(true, true, false),
                    ));

                $deployOptions = new DeployOptions(
                    $deployWallet,
                    $this->environment->deploymentWalletKP->secretKey,
                    Units::toNano("0.05"),
                );
                $fee = $deployer->estimateFee($deployOptions, $exampleContract);
                $this->logger->debug("Deploy estimate fee: " . $fee->toFloat() . " TON");
                $deployer->deploy($deployOptions, $exampleContract);

                $this->logger->debug("Wait for contract \"Active\" state");
                $awaiter = new ContractAwaiter($transport);
                $awaiter->waitForActive($exampleContract->getAddress());

                $this->logger->info("Done!");

                return new TestResult(true, null, null);
            } catch (\Throwable $e) {
                return new TestResult(false, "Unhandled exception", $e);
            }
        });
    }
}
