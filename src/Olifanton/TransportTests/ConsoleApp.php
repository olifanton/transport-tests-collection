<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\TransportTests\Commands\Balance;
use Olifanton\TransportTests\Commands\CaseProxyCommand;
use Olifanton\TransportTests\Commands\Init;
use Olifanton\TransportTests\Commands\RunAll;
use Symfony\Component\Console\Application;

class ConsoleApp extends Application
{
    public function __construct()
    {
        parent::__construct("transport-tests");

        $commands = [
            new Init(),
        ];

        if (Configuration::isCreated()) {
            $cases = Configuration::read()["cases"] ?? [];

            foreach ($cases as $commandName => $caseClass) {
                $commands[] = new CaseProxyCommand($commandName, $caseClass);
            }

            $commands[] = new RunAll();
            $commands[] = new Balance();
        }

        $this->addCommands($commands);
    }
}
