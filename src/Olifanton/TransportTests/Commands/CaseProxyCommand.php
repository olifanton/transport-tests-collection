<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use Olifanton\Interop\Units;
use Olifanton\TransportTests\Exceptions\AssertException;
use Olifanton\TransportTests\Exceptions\TraceMapper;
use Olifanton\TransportTests\ManagedRunner;
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
        $o = new SymfonyStyle($input, $output);
        $logger = new ConsoleLogger($output);

        $runner = new ManagedRunner([
            $this->name => $this->caseClass,
        ]);
        $results = $runner->run($logger, $o);

        $logger->info(sprintf(
            "Spent for cases: %s TON",
            Units::fromNano($results->getTonSpent()),
        ));
        $logger->info(sprintf(
            "Execution time: %f sec.",
            $results->getExecutionTime(),
        ));

        if (!$results->isSuccess()) {
            foreach ($results->failed as $result) {
                $o->error(
                    array_merge(
                        [
                            sprintf("Error: %s", $result->error?->getMessage())
                        ],
                        !$result->error instanceof AssertException ? TraceMapper::map($result->error?->getTrace() ?? []) : [],
                    )
                );
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
