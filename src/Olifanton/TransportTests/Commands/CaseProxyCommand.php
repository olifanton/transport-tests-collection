<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Olifanton\Interop\Units;
use Olifanton\TransportTests\BalanceFetcher;
use Olifanton\TransportTests\Environment;
use Olifanton\TransportTests\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $io = new SymfonyStyle($input, $output);
        $logger = new ConsoleLogger($output);
        $preBalance = BalanceFetcher::getBalance();

        $logger->log(LogLevel::INFO, sprintf(
            "Deployment wallet current balance: %s TON",
            Units::fromNano($preBalance),
        ));

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
            $logger->info(sprintf(
                "Case \"%s\" completed",
                $this->name,
            ));
            sleep(1);
            $logger->info(sprintf(
                "Spent for case: %s TON",
                Units::fromNano($preBalance->minus(BalanceFetcher::getBalance())),
            ));

            return self::SUCCESS;
        }

        $e = $result->error;
        $logger->error(sprintf(
            "Case \"%s\" failed! Error: %s",
            $this->name,
            $e?->getMessage(),
        ));

        if ($e) {
            $io->error(
                array_merge(["Unhandled exception: " . $e->getMessage()], $e->getTrace()),
            );
        }

        return self::FAILURE;
    }
}
