<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Olifanton\Ton\Transports\Toncenter\ToncenterV2Client;

final class TcClientMock
{
    public static ?ToncenterV2Client $mocked = null;
}
