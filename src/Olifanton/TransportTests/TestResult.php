<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Throwable;

class TestResult
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly ?string $message,
        public readonly ?Throwable $error,
    ) {}
}
