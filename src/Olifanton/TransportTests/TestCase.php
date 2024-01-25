<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\Ton\Transport;
use Psr\Log\LoggerInterface;

abstract class TestCase
{
    public function __construct(
        protected readonly Environment $environment,
        protected readonly Context $context,
        protected readonly LoggerInterface $logger,
    ) {}

    abstract public function run(Transport $transport): void;
}
