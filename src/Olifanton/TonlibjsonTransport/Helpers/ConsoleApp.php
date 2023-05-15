<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Helpers;

use Olifanton\TonlibjsonTransport\Helpers\Commands\Download;
use Symfony\Component\Console\Application;

class ConsoleApp extends Application
{
    public function __construct()
    {
        parent::__construct("tonlibjson-transport");

        $this->addCommands([
            new Download(),
        ]);
    }
}
