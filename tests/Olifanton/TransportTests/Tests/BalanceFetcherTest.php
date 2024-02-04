<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Tests;

use Hamcrest\Core\IsEqual;
use Mockery\MockInterface;
use Olifanton\Interop\Units;
use Olifanton\Ton\Transports\Toncenter\ToncenterV2Client;
use Olifanton\TransportTests\BalanceFetcher;
use Olifanton\TransportTests\Environment;
use Olifanton\TransportTests\TcClientMock;
use PHPUnit\Framework\TestCase;

class BalanceFetcherTest extends TestCase
{
    private ToncenterV2Client|MockInterface $toncenterV2ClientMock;

    protected function setUp(): void
    {
        $this->toncenterV2ClientMock = \Mockery::mock(ToncenterV2Client::class); // @phpstan-ignore-line
        TcClientMock::$mocked = $this->toncenterV2ClientMock; // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        TcClientMock::$mocked = null;
        \Mockery::close();
    }

    /**
     * @throws \Throwable
     */
    public function testGetBalance(): void
    {
        // @phpstan-ignore-next-line
        $this
            ->toncenterV2ClientMock
            ->shouldReceive("getAddressBalance")
            ->with(
                IsEqual::equalTo(Environment::getInstance()->deploymentWalletAddr),
            )
            ->andReturn(Units::toNano(100.1));

        $this->assertEquals(
            100.1,
            Units::fromNano(BalanceFetcher::getBalance())->toFloat(),
        );
    }
}
