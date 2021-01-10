<?php
declare(strict_types=1);

namespace App\Library;

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
        $this->credentials = $user . ':' . $pass;
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
     * @throws \RuntimeException
     */
    private function getPage(int $number = 1): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('%s/rec/rec_file.asp?page=%d', $this->url, $number));
        curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $error = null;
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException($error);
        }

        return $content;
    }
}
