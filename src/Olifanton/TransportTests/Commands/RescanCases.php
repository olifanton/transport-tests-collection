<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Olifanton\TransportTests\CasesFinder;
use Olifanton\TransportTests\Configuration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand("rescan")]
class RescanCases extends Command
{
    protected function configure()
    {
        $this->setDescription("Re-scan and save test cases");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!Configuration::isCreated()) {
            $io->error("Configuration file " . Configuration::getPath() . " does not exists");

            return self::FAILURE;
        }

        $config = Configuration::read();
        $config["cases"] = CasesFinder::getCases();

        Configuration::save($config);

        return self::SUCCESS;
    }
}
