<?php declare(strict_types=1);

namespace Olifanton\TransportTests\TcRuntime;

use Olifanton\Ton\Transports\Toncenter\ToncenterTransport;
use Olifanton\TransportTests\TcClient;
use Olifanton\TransportTests\TestCase;
use Olifanton\TransportTests\TestResult;

class Runtime implements \Olifanton\TransportTests\Runtime
{
    protected static ?Runtime $instance = null;

    protected ?ToncenterTransport $transport = null;

    public function setUp(): void
    {
        // Nothing
    }

    public function run(TestCase $case): TestResult
    {
        return $case->run($this->transport);
    }

    public function tearDown(): void
    {
        // Nothing
    }

    public static function create(): \Olifanton\TransportTests\Runtime
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$instance->transport = new ToncenterTransport(
                TcClient::getInstance(),
            );
        }

        return self::$instance;
    }
}
