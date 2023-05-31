<?php declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

define("BIN_LIB_PATH", dirname(__DIR__) . "/lib/");

$binaryName = \Olifanton\TonlibjsonTransport\GenericLocator::locateName();

if (!file_exists(BIN_LIB_PATH . $binaryName)) {
    \Olifanton\TonlibjsonTransport\Downloader::discovered()->download(BIN_LIB_PATH);
}
