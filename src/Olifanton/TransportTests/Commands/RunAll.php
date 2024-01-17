<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand("runall")]
class RunAll extends Command
{
    protected function configure()
    {
        $this->setDescription("Run all test cases");
    }

    /**
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new \RuntimeException("Not implemented");
    }
}
