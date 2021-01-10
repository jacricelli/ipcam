<?php
declare(strict_types=1);

namespace App\Library;

use Cake\Core\Configure;

/**
 * DownloaderFactory
 */
class DownloaderFactory
{
    /**
     * Crea una instancia de la clase Downloader
     *
     * @return \App\Library\Downloader
     */
    public static function create(): Downloader
    {
        return new Downloader(
            Configure::read('url'),
            Configure::read('downloadPath'),
            Configure::read('user'),
            Configure::read('pass')
        );
    }
}
