<?php
declare(strict_types=1);

namespace App\Library;

use Cake\Core\Configure;

/**
 * IPCamFactory
 */
class IPCamFactory
{
    /**
     * Crea una instancia de la clase IPCam
     *
     * @return \App\Library\IPCam
     */
    public static function create(): IPCam
    {
        return new IPCam(
            Configure::read('url'),
            Configure::read('user'),
            Configure::read('pass')
        );
    }
}
