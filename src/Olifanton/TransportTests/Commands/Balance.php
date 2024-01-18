<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;
use Olifanton\TransportTests\Environment;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand("balance")]
class Balance extends Command
{
    protected function configure()
    {
        $this->setDescription("Getting a deployment wallet balance");
    }

    /**
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $env = Environment::getInstance();
        $tc = new ToncenterHttpV2Client(
            new HttpMethodsClient(
                \Http\Discovery\Psr18ClientDiscovery::find(),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
            ),
            new ClientOptions(
                baseUri: "https://testnet.toncenter.com/api/v2",
                apiKey: $env->config["toncenter_api_key"],
            ),
        );
        $balance = $tc->getAddressBalance($env->deploymentWalletAddr);
        $output->writeln(sprintf(
            "Deployment wallet balance: %s TON",
            Units::fromNano($balance),
        ));

        return self::SUCCESS;
    }
}
