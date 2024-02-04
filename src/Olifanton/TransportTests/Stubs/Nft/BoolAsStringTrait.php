<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Stubs\Nft;

use Olifanton\Ton\Contracts\Nft\NftTrait;

class BoolAsStringTrait extends NftTrait
{
    public function __construct(string $traitType, bool $value)
    {
        parent::__construct($traitType, $value ? "Yes" : "No");
    }

    public function valued(bool|int|string|null|float $value): array
    {
        $v = (bool)$value;

        return [
            "type" => $this->traitType,
            "value" => $v ? "Yes" : "No",
        ];
    }
}
