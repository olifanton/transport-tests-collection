<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Cases\Ntf;

use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Nft\MintOptions;
use Olifanton\Ton\Contracts\Nft\NftAttributesCollection;
use Olifanton\Ton\Contracts\Nft\NftCollection;
use Olifanton\Ton\Contracts\Nft\NftCollectionMetadata;
use Olifanton\Ton\Contracts\Nft\NftCollectionOptions;
use Olifanton\Ton\Contracts\Nft\NftItem;
use Olifanton\Ton\Contracts\Nft\NftItemMetadata;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Deployer;
use Olifanton\Ton\DeployOptions;
use Olifanton\Ton\Transport;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\Helpers\JsonserveApi;
use Olifanton\TransportTests\Stubs\Nft\BoolAsStringTrait;
use Olifanton\TransportTests\Stubs\Nft\RarityTrait;
use Olifanton\TransportTests\TestCase;

#[AsCase("nft:mint")]
class MintCollection extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function run(Transport $transport): void
    {
        $collectionMetadata = new NftCollectionMetadata(
            "Average NFT",
            "Yet another test collection",
            "https://ipfs.io/ipfs/QmcTkmxrmFtm98NyUM7UnkKQ2SGSqdvV51FQSj7pfdsSAC",
        );

        $rarityTrait = new RarityTrait();
        $fooTrait = new BoolAsStringTrait("Foo", false);
        $barTrait = new BoolAsStringTrait("Bar", false);

        $attribCollection = new NftAttributesCollection(
            $rarityTrait,
            $fooTrait,
            $barTrait,
        );

        $itemsMetadata = [
            new NftItemMetadata(
                "NFT 0",
                "Yet another NFT",
                "https://ipfs.io/ipfs/QmcTkmxrmFtm98NyUM7UnkKQ2SGSqdvV51FQSj7pfdsSAC",
                attributes: $attribCollection->forItem(
                    $fooTrait->valued(true),
                    $rarityTrait->valued(RarityTrait::RARE),
                ),
            ),
            new NftItemMetadata(
                "NFT 1",
                "Yet another NFT",
                "https://ipfs.io/ipfs/QmcTkmxrmFtm98NyUM7UnkKQ2SGSqdvV51FQSj7pfdsSAC",
                attributes: $attribCollection->forItem(
                    $rarityTrait->valued(RarityTrait::LEGENDARY),
                    $barTrait->valued(true),
                ),
            ),
            new NftItemMetadata(
                "NFT 2",
                "Yet another NFT",
                "https://ipfs.io/ipfs/QmcTkmxrmFtm98NyUM7UnkKQ2SGSqdvV51FQSj7pfdsSAC",
                attributes: $attribCollection->forItem(),
            ),
            new NftItemMetadata(
                "NFT 3",
                "Yet another NFT",
                "https://ipfs.io/ipfs/QmcTkmxrmFtm98NyUM7UnkKQ2SGSqdvV51FQSj7pfdsSAC",
                attributes: $attribCollection->forItem(
                    $fooTrait->valued(true),
                ),
            ),
            new NftItemMetadata(
                "NFT 4",
                "Yet another NFT",
                "https://ipfs.io/ipfs/QmcTkmxrmFtm98NyUM7UnkKQ2SGSqdvV51FQSj7pfdsSAC",
                attributes: $attribCollection->forItem(
                    $rarityTrait->valued(RarityTrait::RARE),
                ),
            ),
        ];

        $this
            ->logger
            ->debug("Metadata created");

        $collectionMetadataUrl = JsonserveApi::putJson($collectionMetadata);
        $itemsMetadataUrls = array_map(static fn (NftItemMetadata $m) => JsonserveApi::putJson($m), $itemsMetadata);

        $this
            ->logger
            ->debug("Metadata uploaded");

        $deployWalletAddress = $this->getDeployWallet()->getAddress();

        $collectionContract = new NftCollection(new NftCollectionOptions(
            $deployWalletAddress,
            $collectionMetadataUrl,
            "",
            NftItem::getDefaultCode(),
        ));

        // Upload collection contract
        $deployer = new Deployer($transport);
        $deployer->setLogger($this->logger);

        $deployer->deploy(
            new DeployOptions(
                $this->getDeployWallet(),
                $this->getSecretKey(),
                Units::toNano(1),
            ),
            $collectionContract,
        );
        $this->assertActiveContract(
            $transport,
            $collectionContract->getAddress(),
            "Collection contract",
        );

        $this->logger->debug("Collection contract deployed, start NFT items iteration");

        $i = 0;
        $nftItemsChunks = array_chunk($itemsMetadataUrls, 4, true);

        foreach ($nftItemsChunks as $chunk) {
            /** @var string[] $chunk */

            $transfers = [];

            foreach ($chunk as $itemMetadataUrl) {
                $transfers[] = new Transfer(
                    $collectionContract->getAddress(),
                    Units::toNano(0.55),
                    NftCollection::createMintBody(new MintOptions(
                        $i,
                        Units::toNano(0.5),
                        $deployWalletAddress,
                        $itemMetadataUrl,
                    )),
                );
                $i++;
            }

            $lastSeqno = (int)$this->getDeployWallet()->seqno($transport);
            $external = $this->getDeployWallet()->createTransferMessage(
                $transfers,
                new TransferOptions($lastSeqno),
            );
            $transport->sendMessage($external, $this->getSecretKey());
            sleep(1);

            do {
                $seqno = (int)$this->getDeployWallet()->seqno($transport);
                sleep(1);
            } while ($lastSeqno === $seqno);

            $this->logger->debug("Chunk deployed");
        }

        $this->assertActiveContract(
            $transport,
            $collectionContract->getNftItemAddress($transport, $i - 1),
            "Last NFT item active",
        );
    }
}
