<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\TonlibjsonTransport\Async\OpenSwoole\OpenSwooleExecutor;
use Olifanton\TonlibjsonTransport\Exceptions\LiteServerError;

require dirname(__DIR__) . "/common.php";

$transport = create_transport(new OpenSwooleExecutor());

\co::run(function () use ($transport) {
    try {
        $response = $transport->execute(new \Olifanton\TonlibjsonTransport\TL\Smc\Load(
            addr("EQCrrSblmeNMAw27AXbchzG6MUja9iac7PHjyK3Xn8EMeqbG"),
        ));
        var_dump($response);
    } catch (LiteServerError $e) {
        var_dump($e);
    }

    $transport->close();
});
