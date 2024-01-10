<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\TransportTests\Commands\Init;
use Symfony\Component\Console\Application;

class ConsoleApp extends Application
{
    public function __construct()
    {
        parent::__construct("transport-tests");

        $this->addCommands([
            new Init(),
        ]);
    }
}
