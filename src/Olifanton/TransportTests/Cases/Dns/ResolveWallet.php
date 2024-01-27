<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Cases\Dns;

use Olifanton\Interop\Address;
use Olifanton\Ton\Dns\DnsClient;
use Olifanton\Ton\Transport;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\TestCase;

#[AsCase("dns:resolve-wallet")]
class ResolveWallet extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function run(Transport $transport): void
    {
        $client = new DnsClient($transport);
        $client->setLogger($this->logger);
        $this
            ->logger
            ->debug("DNS client created");

        $domain = $client->resolve("olifanton.ton");
        $this
            ->logger
            ->debug("Domain resolved");

        $this
            ->assertAddress(
                new Address("0QBfiu4Ta8S1n9cgpnZDKqNlMqiFn2K7GGLEfc-h-GmghFij"),
                $domain->getWallet(),
            );
    }
}
