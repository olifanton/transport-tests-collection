<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Stubs\Nft;

use Olifanton\Ton\Contracts\Nft\NftTrait;

class RarityTrait extends NftTrait
{
    public const COMMON = "Common";
    public const RARE = "Rare";
    public const LEGENDARY = "Legendary";

    public function __construct()
    {
        parent::__construct(
            "Rarity",
            self::COMMON,
        );
    }
}
