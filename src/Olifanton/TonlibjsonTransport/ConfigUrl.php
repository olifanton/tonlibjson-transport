<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport;

enum ConfigUrl : string
{
    case TESTNET = "https://ton.org/testnet-global.config.json";
    case MAINNET = "https://ton.org/global-config.json";
}
