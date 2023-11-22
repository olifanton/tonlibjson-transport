<?php declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\TonlibjsonTransport\ConfigUrl;
use Olifanton\TonlibjsonTransport\TonlibjsonTransportBuilder;
use Olifanton\TonlibjsonTransport\VerbosityLevel;

define("ROOT_DIR", dirname(__DIR__));

require_once ROOT_DIR . "/vendor/autoload.php";

function create_logger(): \Psr\Log\LoggerInterface {
    return new class extends \Psr\Log\AbstractLogger
    {
        public function log($level, \Stringable|string $message, array $context = []): void
        {
            echo "[$level] ", date(DATE_W3C), " ", $message, PHP_EOL;
        }
    };
}

/**
 * @throws \Olifanton\TonlibjsonTransport\Exceptions\BuilderException
 */
function create_transport(\Olifanton\TonlibjsonTransport\Async\Executor $executor, int $timeout = 300): \Olifanton\TonlibjsonTransport\TonlibjsonTransport {
    $transport = (new TonlibjsonTransportBuilder($executor))
        ->setConfigUrl(ConfigUrl::TESTNET)
        ->setLogger(create_logger())
        ->setVerbosityLevel(VerbosityLevel::INFO)
        ->setLibDirectory(ROOT_DIR . "/lib")
        ->build();
    $transport->setTimeout($timeout);

    return $transport;
}

function addr(Address|string $address): Address
{
    return new Address($address);
}
