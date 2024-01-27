<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Olifanton\Interop\Units;
use Olifanton\TransportTests\Configuration;
use Olifanton\TransportTests\Exceptions\AssertException;
use Olifanton\TransportTests\Exceptions\TraceMapper;
use Olifanton\TransportTests\ManagedRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $o = new SymfonyStyle($input, $output);
        $logger = new ConsoleLogger($output);

        $runner = new ManagedRunner(Configuration::read()["cases"] ?? []);
        $results = $runner->run($logger, $o);

        foreach ($results->successful as $name => $result) {
            $o->success(sprintf(
                "%s: %f sec., spent: %s TON",
                $name,
                $result->executionTime,
                Units::fromNano($result->spent),
            ));
        }

        foreach ($results->failed as $name => $result) {
            $o->error(
                array_merge(
                    [
                        sprintf(
                            "%s: Error: %s",
                            $name,
                            $result->error?->getMessage(),
                        )
                    ],
                    !$result->error instanceof AssertException ? TraceMapper::map($result->error?->getTrace() ?? []) : [],
                )
            );
        }

        $o->newLine();

        $o->info([
            sprintf("Spent for all cases: %s TON", Units::fromNano($results->getTonSpent())),
            sprintf("Execution time: %f sec.", $results->getExecutionTime()),
        ]);

        return $results->isSuccess() ? self::SUCCESS : self::FAILURE;
    }
}
