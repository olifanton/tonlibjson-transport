<?php declare(strict_types=1);

namespace Olifanton\TonlibjsonTransport\Helpers\Commands;

use Olifanton\TonlibjsonTransport\Downloader;
use Olifanton\TonlibjsonTransport\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class Download extends Command
{
    protected static $defaultName = "download";

    public function configure(): void
    {
        $this
            ->setDescription("Download native library binaries")
            ->addArgument(
                "path",
                InputArgument::REQUIRED,
                "Target directory",
            )
            ->addOption(
                "--platform",
                "-p",
                InputArgument::OPTIONAL,
                "Target platform. Supported platforms: " . implode(', ', array_map(fn(Platform $platform) => $platform->value, Platform::cases())),
                "auto",
            );
    }

    /**
     * @throws \Http\Client\Exception
     * @throws \Olifanton\TonlibjsonTransport\Exceptions\LibraryLocationException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $platformCode = $input->hasOption('platform') ? $input->getOption('platform') : "auto";
        $platform = $platformCode === "auto" ? null : Platform::from($platformCode);
        $logger = new ConsoleLogger($output);

        $downloader = Downloader::discovered();
        $downloader->setLogger($logger);

        $downloader->download($input->getArgument("path"), $platform);

        return self::SUCCESS;
    }
}
