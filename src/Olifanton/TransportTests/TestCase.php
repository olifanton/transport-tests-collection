<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\Interop\KeyPair;
use Olifanton\Ton\Transport;
use Psr\Log\LoggerInterface;

abstract class TestCase
{
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly KeyPair $keyPair,
    ) {}

    abstract public function run(Transport $transport): TestResult;
}
