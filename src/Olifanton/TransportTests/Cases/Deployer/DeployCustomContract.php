<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Cases\Deployer;

use Olifanton\Ton\Transport;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\TestCase;
use Olifanton\TransportTests\TestResult;

#[AsCase("deployer:custom-contract")]
class DeployCustomContract extends TestCase
{
    public function run(Transport $transport): TestResult
    {
        return new TestResult(true, "Message", null);
    }
}
