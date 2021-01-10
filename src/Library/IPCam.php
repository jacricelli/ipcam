<?php
declare(strict_types=1);

namespace App\Library;

use ErrorException;
use Exception;

/**
 * IPCam
 */
class IPCam
{
    /**
     * URL
     *
     * @var string
     */
    private string $url;

    /**
     * Credenciales
     *
     * @var string
     */
    private string $credentials;

    /**
     * Propiedades
     *
     * @var \App\Library\Device|null
     */
    private ?Device $device = null;

    /**
     * Constructor
     *
     * @param string $url URL
     * @param string $user Usuario
     * @param string $pass Contraseña
     */
    public function __construct(string $url, string $user, string $pass)
    {
        $this->url = $url;
        $this->credentials = base64_encode($user . ':' . $pass);
    }

    /**
     * Obtiene la URL
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Obtiene las propiedades del dispositivo y de la página actual
     *
     * @return \App\Library\Device|null
     * @throws \Exception
     */
    public function getDevice(): ?Device
    {
        if (!$this->device) {
            $content = $this->getPage();

            $parser = new PageParser($content);
            $this->device = new Device($parser->getProperties());
        }

        return $this->device;
    }

    /**
     * Obtiene las grabaciones de la página especificada
     *
     * @param int $pageNumber Número de página
     * @return \App\Library\RecordingCollection
     * @throws \Exception
     * @throws \ErrorException
     */
    public function getRecordings(int $pageNumber = 1): RecordingCollection
    {
        $content = $this->getPage($pageNumber);

        $parser = new PageParser($content);
        $this->device = new Device($parser->getProperties());

        return $parser->getRecordings();
    }

    /**
     * Obtiene el contenido de la página especificada
     *
     * @param int $number Número de página
     * @return string
     * @throws \ErrorException
     */
    private function getPage(int $number = 1): string
    {
        set_error_handler(
            function ($severity, $message, $file, $line) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw new ErrorException($message, $severity, $severity, $file, $line);
            }
        );

        try {
            return file_get_contents(
                sprintf('%s/rec/rec_file.asp?page=%d', $this->url, $number),
                false,
                stream_context_create([
                    'http' => [
                        'header' => sprintf('Authorization: Basic %s', $this->credentials),
                    ],
                ])
            );
        } catch (Exception $e) {
            throw $e;
        } finally {
            restore_error_handler();
        }
    }
}
