<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\TonlibjsonTransport\Pool\Client\Factories\SwoolePoolFactory;
use Olifanton\TonlibjsonTransport\TL\GetAccountState;
use Olifanton\TonlibjsonTransport\TonlibjsonTransportBuilder;

define("ROOT_DIR", dirname(__DIR__));
require_once ROOT_DIR . "/vendor/autoload.php";

$logger = new class extends \Psr\Log\AbstractLogger
{
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        echo "[$level] ", date(DATE_W3C), " ", $message, PHP_EOL;
    }
};

$builder = (new TonlibjsonTransportBuilder(false))
    ->setLogger($logger)
    ->setLibDirectory(ROOT_DIR . "/lib")
    ->setClientPoolFactory(new SwoolePoolFactory());
$transport = $builder->build();

\co::run(function () use ($transport) {
    $response = $transport->execute(new GetAccountState(
        new \Olifanton\Interop\Address("EQCrrSblmeNMAw27AXbchzG6MUja9iac7PHjyK3Xn8EMeqbG"),
    ));
    $accountState = \Olifanton\Ton\Marshalling\Json\Hydrator::extract(\Olifanton\Ton\Transports\Toncenter\Responses\ExtendedFullAccountState::class, $response);

    var_dump($accountState);

    $transport->close();
});
