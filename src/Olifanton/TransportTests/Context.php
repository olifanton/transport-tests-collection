<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

interface Context
{
    public function assert(bool $condition, string $message): void;
}
