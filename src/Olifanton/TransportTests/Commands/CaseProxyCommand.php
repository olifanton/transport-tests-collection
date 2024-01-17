<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CaseProxyCommand extends Command
{
    public function __construct(
        private readonly string $name,
        private readonly string $caseClass,
    )
    {
        parent::__construct($this->name);
    }

    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("");
    }

    /**
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new \RuntimeException("Not implemented");
    }
}
