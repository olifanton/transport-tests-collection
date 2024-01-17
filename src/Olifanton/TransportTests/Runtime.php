<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

interface Runtime
{
    public function setUp(): void;

    public function run(callable $caseCallable): TestResult;

    public function tearDown(): void;
}
