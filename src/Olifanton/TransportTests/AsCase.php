<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsCase
{
    public function __construct(public string $alias) {}
}
