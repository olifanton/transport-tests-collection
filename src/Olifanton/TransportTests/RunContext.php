<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\TransportTests\Exceptions\AssertException;

class RunContext implements Context
{
    private int $assertions = 0;

    public function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new AssertException("Assertion failed: $message");
        }

        $this->assertions++;
    }

    public function getAssertionsCount(): int
    {
        return $this->assertions;
    }
}
