<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\TonlibjsonTransport\Async\React\ReactExecutor;
use Olifanton\TonlibjsonTransport\ConfigUrl;
use Olifanton\TonlibjsonTransport\Exceptions\LiteServerError;
use Olifanton\TonlibjsonTransport\TL\GetAccountState;
use Olifanton\TonlibjsonTransport\TonlibjsonTransportBuilder;
use Olifanton\TonlibjsonTransport\VerbosityLevel;

define("ROOT_DIR", dirname(__DIR__, 2));
require_once ROOT_DIR . "/vendor/autoload.php";

$logger = new class extends \Psr\Log\AbstractLogger
{
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        echo "[$level] ", date(DATE_W3C), " ", $message, PHP_EOL;
    }
};

$builder = (new TonlibjsonTransportBuilder(new ReactExecutor()))
    ->setConfigUrl(ConfigUrl::TESTNET)
    ->setLogger($logger)
    ->setVerbosityLevel(VerbosityLevel::INFO)
    ->setLibDirectory(ROOT_DIR . "/lib");
$transport = $builder->build();

\React\Async\async(function () use ($transport) {
    try {
        $response = $transport->execute(new GetAccountState(
            new \Olifanton\Interop\Address("EQCrrSblmeNMAw27AXbchzG6MUja9iac7PHjyK3Xn8EMeqbG"),
        ));
        $accountState = \Olifanton\Ton\Marshalling\Json\Hydrator::extract(\Olifanton\Ton\Transports\Toncenter\Responses\ExtendedFullAccountState::class, $response);
        var_dump($accountState);
    } catch (LiteServerError $e) {
        var_dump($e);
    }

    $transport->close();
})();
