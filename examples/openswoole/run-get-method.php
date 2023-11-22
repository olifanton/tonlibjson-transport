<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\TonlibjsonTransport\Async\OpenSwoole\OpenSwooleExecutor;
use Olifanton\TonlibjsonTransport\Exceptions\LiteServerError;

require dirname(__DIR__) . "/common.php";

$transport = create_transport(new OpenSwooleExecutor());

\co::run(function () use ($transport) {
    try {
        $stack = $transport->runGetMethod(
            addr("EQCrrSblmeNMAw27AXbchzG6MUja9iac7PHjyK3Xn8EMeqbG"),
            "seqno",
        );
        var_dump($stack->currentBigInteger());
    } catch (LiteServerError $e) {
        var_dump($e);
    }

    $transport->close();
});
