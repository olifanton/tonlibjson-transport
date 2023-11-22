<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\TonlibjsonTransport\Async\React\ReactExecutor;
use Olifanton\TonlibjsonTransport\Exceptions\LiteServerError;
use Olifanton\TonlibjsonTransport\TL\GetAccountState;

require dirname(__DIR__) . "/common.php";

$transport = create_transport(new ReactExecutor());
$transport->setTimeout(300);

\React\Async\async(function () use ($transport) {
    try {
        $response = $transport->execute(new GetAccountState(
            addr("EQCrrSblmeNMAw27AXbchzG6MUja9iac7PHjyK3Xn8EMeqbG"),
        ));
        $accountState = \Olifanton\Ton\Marshalling\Json\Hydrator::extract(\Olifanton\Ton\Transports\Toncenter\Responses\ExtendedFullAccountState::class, $response);
        var_dump($accountState);
    } catch (LiteServerError $e) {
        var_dump($e);
    }

    $transport->close();
})();
