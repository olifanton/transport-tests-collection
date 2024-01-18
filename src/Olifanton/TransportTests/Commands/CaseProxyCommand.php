<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Olifanton\TransportTests\Environment;
use Olifanton\TransportTests\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
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
        $logger = new ConsoleLogger($output);
        $env = Environment::getInstance();
        $runtime = $env->getRuntime();
        /** @var TestCase $case */
        $case = new $this->caseClass(
            $env,
            $logger,
        );
        $runtime->setUp();
        $result = $runtime->run($case);
        $runtime->tearDown();

        if ($result->isSuccess) {
            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
