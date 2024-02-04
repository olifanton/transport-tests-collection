<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Tests\Commands;

use Hamcrest\Core\IsEqual;
use Hamcrest\Core\IsInstanceOf;
use Mockery\MockInterface;
use Olifanton\Interop\Units;
use Olifanton\Ton\Transports\Toncenter\ToncenterV2Client;
use Olifanton\TransportTests\Commands\RunAll;
use Olifanton\TransportTests\Environment;
use Olifanton\TransportTests\Runtime;
use Olifanton\TransportTests\TcClientMock;
use Olifanton\TransportTests\Tests\Stubs\FooBarCase;
use Olifanton\TransportTests\Tests\Stubs\MockableRuntime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\NullOutput;

class RunAllTest extends TestCase
{
    private ToncenterV2Client|MockInterface $toncenterV2ClientMock;

    private Runtime|MockInterface $runtimeMock;

    protected function setUp(): void
    {
        $this->toncenterV2ClientMock = \Mockery::mock(ToncenterV2Client::class); // @phpstan-ignore-line
        $this->runtimeMock = \Mockery::mock(Runtime::class); // @phpstan-ignore-line
        TcClientMock::$mocked = $this->toncenterV2ClientMock; // @phpstan-ignore-line
        MockableRuntime::setMock($this->runtimeMock);  // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        TcClientMock::$mocked = null;
        \Mockery::close();
    }

    /**
     * @throws \Throwable
     */
    public function testExecute(): void
    {
        // Console mocks
        $inputMock = \Mockery::mock(Input::class);

        $inputMock->shouldReceive("bind"); // @phpstan-ignore-line
        $inputMock->shouldReceive("isInteractive")->andReturnFalse();  // @phpstan-ignore-line
        $inputMock->shouldReceive("hasArgument")->andReturnFalse();  // @phpstan-ignore-line
        $inputMock->shouldReceive("validate");  // @phpstan-ignore-line

        // Balance mock
        // @phpstan-ignore-next-line
        $this
            ->toncenterV2ClientMock
            ->shouldReceive("getAddressBalance")
            ->with(IsEqual::equalTo(Environment::getInstance()->deploymentWalletAddr))
            ->andReturn(Units::toNano(10));

        // Runtime mock
        // @phpstan-ignore-next-line
        $this
            ->runtimeMock
            ->shouldReceive("run")
            ->with(IsInstanceOf::anInstanceOf(FooBarCase::class));

        // Test
        $this->assertEquals(
            Command::SUCCESS,
            (new RunAll())->run(
                $inputMock, // @phpstan-ignore-line
                new NullOutput(),
            ),
        );
    }
}
