<?php return [
    'deployment_wallet' => [
        'secret_key' => 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==',
        'class' => 'Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2',
        'address' => 'EQCg5fZTvtgMoA8SoJ6GA01Q8SNfQ-X55XgkOMiEiZOP8TJV',
    ],
    'toncenter_api_key' => '0000000000000000000000000000000000000000000000000000000000000000',
    'cases' => [
        'foo:bar' => 'Olifanton\TransportTests\Tests\Stubs\FooBarCase'
    ],
    'env' => 'Olifanton\TransportTests\Environment',
    'runtime' => 'Olifanton\TransportTests\Tests\Stubs\MockableRuntime',
];
