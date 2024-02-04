<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Tests\Stubs;

use Olifanton\TransportTests\Runtime;
use Olifanton\TransportTests\TestCase;

class MockableRuntime implements Runtime
{
    protected static Runtime|null $runtimeMock = null;

    public function setUp(): void {}

    public function run(TestCase $case): void
    {
        if (!self::$runtimeMock) {
            throw new \RuntimeException();
        }

        self::$runtimeMock->run($case);
    }

    public function tearDown(): void
    {
        self::$runtimeMock = null;
    }

    public static function create(): Runtime
    {
        return new self();
    }

    public static function setMock(?Runtime $mock): void
    {
        self::$runtimeMock = $mock;
    }
}
