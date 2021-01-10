<?php
declare(strict_types=1);

namespace App\Library;

use Cake\Console\ConsoleIo;
use Cake\Core\Configure;

/**
 * DownloaderFactory
 */
class DownloaderFactory
{
    /**
     * Crea una instancia de la clase Downloader
     *
     * @param \Cake\Console\ConsoleIo|null $io Instancia de ConsoleIo
     * @return \App\Library\Downloader
     */
    public static function create(?ConsoleIo $io = null): Downloader
    {
        return new Downloader(
            Configure::read('url'),
            Configure::read('downloadPath'),
            Configure::read('user'),
            Configure::read('pass'),
            $io
        );
    }
}
