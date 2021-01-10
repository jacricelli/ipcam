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
     * @var \stdClass|null
     */
    private ?\stdClass $properties = null;

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
        $this->properties = $parser->getProperties();

        return $parser->getRecordings();
    }

    /**
     * Obtiene el espacio total
     *
     * @return int
     * @throws \Exception
     */
    public function getTotalSpace(): int
    {
        if (!$this->properties) {
            $this->getRecordings();
        }

        return $this->properties->SdcardTotalSpace ?? 0;
    }

    /**
     * Obtiene el espacio libre
     *
     * @return int
     * @throws \Exception
     */
    public function getFreeSpace(): int
    {
        if (!$this->properties) {
            $this->getRecordings();
        }

        return $this->properties->SdcardFreeSpace ?? 0;
    }

    /**
     * Obtiene el total de grabaciones
     *
     * @return int
     * @throws \Exception
     */
    public function getTotalRecordings(): int
    {
        if (!$this->properties) {
            $this->getRecordings();
        }

        return $this->properties->RecFilesTotal ?? 0;
    }

    /**
     * Obtiene la cantidad de grabaciones por página
     *
     * @return int
     * @throws \Exception
     */
    public function getRecordingsPerPage(): int
    {
        if (!$this->properties) {
            $this->getRecordings();
        }

        return $this->properties->PageMaxitem ?? 0;
    }

    /**
     * Obtiene el total de páginas
     *
     * @return int
     * @throws \Exception
     */
    public function getTotalPages(): int
    {
        if (!$this->properties) {
            $this->getRecordings();
        }

        if (isset($this->properties->RecFilesTotal, $this->properties->PageMaxitem)) {
            return (int)round($this->properties->RecFilesTotal / $this->properties->PageMaxitem);
        }

        return 0;
    }

    /**
     * Indica si la cámara se encuentra grabando
     *
     * @return bool
     * @throws \Exception
     */
    public function isRunning(): bool
    {
        if (!$this->properties) {
            $this->getRecordings();
        }

        return isset($this->properties->RecRuning) ? (bool)$this->properties->RecRuning : false;
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
