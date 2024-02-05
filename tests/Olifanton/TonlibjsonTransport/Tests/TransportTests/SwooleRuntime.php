<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\TransportTests;

use Olifanton\TonlibjsonTransport\Async\OpenSwoole\OpenSwooleExecutor;
use Olifanton\TonlibjsonTransport\ConfigUrl;
use Olifanton\TonlibjsonTransport\TonlibjsonTransport;
use Olifanton\TonlibjsonTransport\TonlibjsonTransportBuilder;
use Olifanton\TonlibjsonTransport\VerbosityLevel;
use Olifanton\TransportTests\TestCase;

class SwooleRuntime implements \Olifanton\TransportTests\Runtime
{
    private static ?SwooleRuntime $instance = null;

    private ?TonlibjsonTransport $transport;

    /**
     * @throws \Olifanton\TonlibjsonTransport\Exceptions\BuilderException
     */
    public function setUp(): void
    {
        \Co::set(['hook_flags' => \OpenSwoole\Runtime::HOOK_ALL]);

        $this->transport = (new TonlibjsonTransportBuilder(new OpenSwooleExecutor()))
            ->setConfigUrl(ConfigUrl::TESTNET)
            ->setVerbosityLevel(VerbosityLevel::INFO)
            ->setLibDirectory(getcwd() . "/lib")
            ->build();
        $this->transport->setTimeout(300);
    }

    public function run(TestCase $case): void
    {
        fwrite(STDOUT, "KEKE");
        $r = \Co::run(function () use (&$case) {
            fwrite(STDOUT, "KEKE");
            $case->run($this->transport);
        });
        fwrite(STDOUT, (string)$r);
    }

    public function tearDown(): void
    {
        if ($this->transport) {
            $this->transport->close();
            $this->transport = null;
        }
    }

    public static function create(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
