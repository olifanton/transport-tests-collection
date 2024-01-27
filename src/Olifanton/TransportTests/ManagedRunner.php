<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\TransportTests\Exceptions\AssertException;
use Olifanton\TransportTests\Exceptions\TraceMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ManagedRunner
{
    /**
     * @param array<string, class-string<TestCase>> $cases
     */
    public function __construct(
        private readonly array $cases,
    ) {}

    /**
     * @throws \Throwable
     */
    public function run(LoggerInterface $logger, SymfonyStyle $o): TestResultCollection
    {
        $successful = [];
        $failed = [];

        $assertions = 0;
        $failedAssertions = 0;

        $env = Environment::getInstance();
        $runtime = $env->getRuntime();
        $runtime->setUp();
        $previousBalance = BalanceFetcher::getBalance();

        foreach ($this->cases as $name => $caseClass) {
            $timeStart = microtime(true);
            $ctx = new RunContext();

            try {
                /** @var TestCase $case */
                $case = new $caseClass(
                    $env,
                    $ctx,
                    $logger,
                );
                $runtime->run($case);
                $executionTime = microtime(true) - $timeStart;
                $logger->info(sprintf(
                    "Case \"%s\" completed",
                    $name,
                ));
                sleep(1);
                $currentBalance = BalanceFetcher::getBalance();
                $spent = $previousBalance->minus($currentBalance);
                $previousBalance = $currentBalance;
                $assertions += $ctx->getAssertionsCount();
                $successful[$name] = new TestResult(
                    true,
                    $spent,
                    $executionTime,
                    $ctx->getAssertionsCount(),
                    null,
                );
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (AssertException $e) {
                $failedAssertions++;
                $currentBalance = BalanceFetcher::getBalance();
                $spent = $previousBalance->minus($currentBalance);
                $previousBalance = $currentBalance;
                $failed[$name] = new TestResult(
                    false,
                    $spent,
                    microtime(true) - $timeStart,
                    0,
                    $e,
                );
                continue;
            } catch (\Throwable $e) {
                $o->error(array_merge([sprintf(
                    "Case \"%s\" failed! Unhandled exception: %s",
                    $name,
                    $e->getMessage(),
                )], TraceMapper::map($e->getTrace())));
                break;
            }
        }

        $runtime->tearDown();

        return new TestResultCollection(
            $successful,
            $failed,
            $assertions,
            $failedAssertions,
        );
    }
}
