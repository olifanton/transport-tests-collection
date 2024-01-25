<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Brick\Math\BigInteger;
use Throwable;

class TestResult
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly BigInteger $spent,
        public readonly float $executionTime,
        public int $assertions,
        public readonly ?Throwable $error,
    ) {}
}
