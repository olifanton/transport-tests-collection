<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Tests\Stubs;

use Olifanton\Ton\Transport;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\TestCase;

#[AsCase("foo:bar")]
class FooBarCase extends TestCase
{
    /**
     * @inheritDoc
     */
    public function run(Transport $transport): void {}
}
