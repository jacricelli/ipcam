<?php
declare(strict_types=1);

namespace IPCam;

/**
 * IPCamFactory
 */
class IPCamFactory
{
    /**
     * Crea una instancia de la clase IPCam
     *
     * @return \IPCam\IPCam
     */
    public static function create(): IPCam
    {
        foreach (['IPCAM_URL', 'IPCAM_USER', 'IPCAM_PASS'] as $key) {
            if (empty($_ENV[$key])) {
                throw new \RuntimeException("No se ha configurado $key.");
            }
        }

        return new IPCam($_ENV['IPCAM_URL'], $_ENV['IPCAM_USER'], $_ENV['IPCAM_PASS']);
    }
}
