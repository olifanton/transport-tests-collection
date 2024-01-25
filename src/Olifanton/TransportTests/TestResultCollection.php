<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Brick\Math\BigInteger;

class TestResultCollection
{
    /**
     * @param array<string, TestResult> $successful
     * @param array<string, TestResult> $failed
     */
    public function __construct(
        public readonly array $successful,
        public readonly array $failed,
        public int $successfulAssertions,
        public int $failedAssertions,
    ) {}

    public function getTonSpent(): BigInteger
    {
        $cFn = static fn (BigInteger $c, TestResult $r) => $c->plus($r->spent);

        return array_reduce(
            $this->successful,
            $cFn,
            BigInteger::zero(),
        )
            ->plus(
                array_reduce(
                    $this->failed,
                    $cFn,
                    BigInteger::zero(),
                )
            );
    }

    public function getExecutionTime(): float
    {
        $cFn = static fn (float $c, TestResult $r) => $c + $r->executionTime;

        return array_reduce($this->successful, $cFn, 0) + array_reduce($this->failed, $cFn, 0);
    }

    public function isSuccess(): bool
    {
        return !empty($this->successful) && empty($this->failed);
    }
}
