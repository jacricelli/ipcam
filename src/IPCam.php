<?php
declare(strict_types=1);

namespace IPCam;

/**
 * IPCam
 */
class IPCam
{
    // phpcs:disable
    /**
     * Constructor
     *
     * @param string $url URL
     * @param string $user Usuario
     * @param string $pass Contraseña
     */
    public function __construct(
        public string $url,
        public string $user,
        public string $pass,
    ) {
        $this->url = rtrim($this->url, '/');
    }
    // phpcs:enable

//    /**
//     * Obtiene las grabaciones de la página especificada
//     *
//     * @param int $pageNumber Número de página
//     * @return \App\Library\RecordingCollection
//     * @throws \Exception
//     */
//    public function getRecordings(int $pageNumber = 1): RecordingCollection
//    {
//        $content = $this->getPage($pageNumber);
//
//        $parser = new PageParser($content);
//        $this->properties = $parser->getProperties();
//
//        return $parser->getRecordings();
//    }
//
//    /**
//     * Descarga las grabaciones de la página especificada
//     *
//     * @param int $pageNumber Número de página
//     * @param array $skip El número de una o más grabaciones que no deben descargarse
//     * @return void
//     * @throws \Exception
//     */
//    public function downloadRecordings(int $pageNumber = 1, array $skip = []): void
//    {
//        $recordings = $this->getRecordings($pageNumber);
//
//        $downloader = DownloaderFactory::create($this->io);
//        $downloader->downloadCollection($recordings, $skip);
//    }
//
//    /**
//     * Elimina las grabaciones de la página especificada
//     *
//     * @param int $pageNumber Número de página
//     * @return void
//     * @throws \Exception
//     */
//    public function deleteRecordings(int $pageNumber = 1): void
//    {
//        $recordings = $this->getRecordings($pageNumber);
//        if (!$recordings->count()) {
//            throw new \RuntimeException('La colección de grabaciones está vacía.');
//        }
//
//        foreach ($recordings as $recording) {
//            if (!$this->deleteRecording($recording)) {
//                throw new \RuntimeException(
//                    sprintf('No se pudo eliminar la grabación \'%s\'.', $recording->getFilename())
//                );
//            }
//            $this->io->out(sprintf('Eliminado \'%s\'.', $recording->getFilename()));
//        }
//    }
//
//    /**
//     * Obtiene el espacio total
//     *
//     * @return int
//     * @throws \Exception
//     */
//    public function getTotalSpace(): int
//    {
//        if (!$this->properties) {
//            $this->getRecordings();
//        }
//
//        return $this->properties->SdcardTotalSpace ?? 0;
//    }
//
//    /**
//     * Obtiene el espacio libre
//     *
//     * @return int
//     * @throws \Exception
//     */
//    public function getFreeSpace(): int
//    {
//        if (!$this->properties) {
//            $this->getRecordings();
//        }
//
//        return $this->properties->SdcardFreeSpace ?? 0;
//    }
//
//    /**
//     * Obtiene el total de grabaciones
//     *
//     * @return int
//     * @throws \Exception
//     */
//    public function getTotalRecordings(): int
//    {
//        if (!$this->properties) {
//            $this->getRecordings();
//        }
//
//        return $this->properties->RecFilesTotal ?? 0;
//    }
//
//    /**
//     * Obtiene la cantidad de grabaciones por página
//     *
//     * @return int
//     * @throws \Exception
//     */
//    public function getRecordingsPerPage(): int
//    {
//        if (!$this->properties) {
//            $this->getRecordings();
//        }
//
//        return $this->properties->PageMaxitem ?? 0;
//    }
//
//    /**
//     * Obtiene el total de páginas
//     *
//     * @return int
//     * @throws \Exception
//     */
//    public function getTotalPages(): int
//    {
//        if (!$this->properties) {
//            $this->getRecordings();
//        }
//
//        if (isset($this->properties->RecFilesTotal, $this->properties->PageMaxitem)) {
//            return (int)ceil($this->properties->RecFilesTotal / $this->properties->PageMaxitem);
//        }
//
//        return 0;
//    }
//
//    /**
//     * Indica si la cámara se encuentra grabando
//     *
//     * @return bool
//     * @throws \Exception
//     */
//    public function isRunning(): bool
//    {
//        if (!$this->properties) {
//            $this->getRecordings();
//        }
//
//        return isset($this->properties->RecRuning) ? (bool)$this->properties->RecRuning : false;
//    }
//
//    /**
//     * Reinicia el dispositivo
//     *
//     * @return void
//     * @throws \RuntimeException
//     */
//    public function reboot(): void
//    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, sprintf('%s/media/?action=cmd&code=255&value=255', $this->url));
//        curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_FAILONERROR, true);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
//
//        $error = null;
//        curl_exec($ch);
//        if (curl_errno($ch)) {
//            $error = curl_error($ch);
//        }
//        curl_close($ch);
//
//        if ($error && stripos($error, 'Operation timed out after') === false) {
//            throw new \RuntimeException($error);
//        }
//    }
//
//    /**
//     * Formatea el almacenamiento del dispositivo
//     *
//     * @return string
//     * @throws \RuntimeException
//     */
//    public function format(): string
//    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, sprintf('%s/rec_action.cgi?op=fmt', $this->url));
//        curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_FAILONERROR, true);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
//
//        $error = null;
//        $content = curl_exec($ch);
//        if (curl_errno($ch)) {
//            $error = curl_error($ch);
//        }
//        curl_close($ch);
//
//        if (is_string($content)) {
//            $content = strtolower(trim($content));
//            if ($content !== 'ok') {
//                $error = 'Se produjo un error interno al formatear el almacenamiento.';
//            }
//        }
//
//        if ($error) {
//            throw new \RuntimeException($error);
//        }
//
//        return $content;
//    }
//
//    /**
//     * Obtiene el contenido de la página especificada
//     *
//     * @param int $number Número de página
//     * @return string
//     * @throws \RuntimeException
//     */
//    private function getPage(int $number = 1): string
//    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, sprintf('%s/rec/rec_file.asp?page=%d', $this->url, $number));
//        curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_FAILONERROR, true);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
//
//        $error = null;
//        $content = curl_exec($ch);
//        if (curl_errno($ch)) {
//            $error = curl_error($ch);
//        }
//        curl_close($ch);
//
//        if ($error) {
//            throw new \RuntimeException($error);
//        }
//
//        return $content;
//    }
//
//    /**
//     * Elimina una grabación
//     *
//     * @param \App\Library\RecordingInterface $recording Grabación
//     * @return bool
//     * @throws \RuntimeException
//     */
//    private function deleteRecording(RecordingInterface $recording): bool
//    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, sprintf(
//            '%s/rec_action.cgi?op=del&fl=%s',
//            $this->url,
//            urlencode($recording->getFilename())
//        ));
//        curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_FAILONERROR, true);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
//
//        $error = null;
//        curl_exec($ch);
//        if (curl_errno($ch)) {
//            $error = curl_error($ch);
//        }
//        curl_close($ch);
//
//        if ($error) {
//            throw new \RuntimeException($error);
//        }
//
//        return true;
//    }
}
