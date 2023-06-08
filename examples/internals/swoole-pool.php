<?php declare(strict_types=1);

use Olifanton\TonlibjsonTransport\Tonlibjson\TonlibInstance;

require_once dirname(__DIR__, 2) . "/vendor/autoload.php";
co::set([
    'hook_flags' => OpenSwoole\Runtime::HOOK_SLEEP,
]);

$logger = new class extends \Psr\Log\AbstractLogger
{
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        echo "[$level] ", date(DATE_W3C), " ", $message, PHP_EOL;
    }
};

$tonlib = new TonlibInstance((new \Olifanton\TonlibjsonTransport\GenericLocator(dirname(__DIR__, 2) . "/lib"))->locatePath());
$pool = new \Olifanton\TonlibjsonTransport\Pool\DynamicPool($tonlib, 5);
$pool->setLogger($logger);

co::run(function() use ($pool, $logger) {
    for ($i = 0; $i < 20; $i++) {
        go(function() use ($pool, $logger) {
            $client = $pool->borrow();
            $sleepSec = rand(1, 5);
            sleep($sleepSec);
            $logger->debug("Sleep " . $sleepSec . " sec");
            $pool->return($client);
        });
    }
});
