<?php
declare(strict_types=1);

namespace App\Library;

use Cake\Console\ConsoleIo;
use Cake\Core\Configure;

/**
 * IPCamFactory
 */
class IPCamFactory
{
    /**
     * Crea una instancia de la clase IPCam
     *
     * @param \Cake\Console\ConsoleIo|null $io Instancia de ConsoleIo
     * @return \App\Library\IPCam
     */
    public static function create(?ConsoleIo $io = null): IPCam
    {
        return new IPCam(
            Configure::read('url'),
            Configure::read('user'),
            Configure::read('pass'),
            $io
        );
    }
}
