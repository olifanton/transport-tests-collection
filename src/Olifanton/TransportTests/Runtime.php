<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

interface Runtime
{
    public function setUp(): void;

    public function run(TestCase $case): TestResult;

    public function tearDown(): void;

    public static function create(): self;
}
