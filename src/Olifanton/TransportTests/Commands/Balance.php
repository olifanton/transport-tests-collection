<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Olifanton\Interop\Units;
use Olifanton\TransportTests\BalanceFetcher;
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
        $output->writeln(sprintf(
            "Deployment wallet balance: %s TON",
            Units::fromNano(BalanceFetcher::getBalance()),
        ));

        return self::SUCCESS;
    }
}
