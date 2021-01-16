<?php
declare(strict_types=1);

namespace IPCam;

/**
 * IPCam
 */
class IPCam
{
    /**
     * Dispositivo
     *
     * @var Device
     */
    private Device $device;

    // phpcs:disable
    /**
     * Constructor
     *
     * @param string $url URL
     * @param string $user Usuario
     * @param string $pass Contraseña
     */
    public function __construct(
        private string $url,
        private string $user,
        private string $pass,
    ) {
        $this->url = rtrim($this->url, '/');
    }
    // phpcs:enable

    /**
     * Obtiene una instancia de la clase Device configurada con las propiedades del dispositivo actual
     *
     * @return Device
     * @throws \Exception
     */
    public function getDevice(): Device
    {
        if (!isset($this->device)) {
            $html = $this->doRequest('rec/rec_file.asp');
            $content = $this->getRelevantContent($html);

            preg_match_all('/var ([A-Za-z_]+) = (\d+);/', $content, $matches);

            $properties = [];
            foreach ($matches[1] as $index => $name) {
                $properties[$name] = (int)$matches[2][$index];
            }

            $this->device = new Device($properties);
        }

        return $this->device;
    }

    /**
     * Obtiene las grabaciones
     *
     * @return RecordingCollection
     * @throws \Exception
     */
    public function getRecordings(): RecordingCollection
    {
        $collection = new RecordingCollection();
        $pages = $this->getDevice()->getTotalPages();

        for ($page = $pages; $page >= 1; $page--) {
            $html = $this->doRequest('rec/rec_file.asp?page=' . $page);
            $content = $this->getRelevantContent($html);

            preg_match_all('/^rec_files(_size|_recstart|_recend)?\[\d{1,2}]+\s=(.+);/m', $content, $matches);

            foreach (array_chunk($matches[2], 4) as $chunk) {
                $collection->add(new Recording(
                    trim($chunk[0], "'"),
                    (int)$chunk[1],
                    new \DateTimeImmutable(trim($chunk[2], "'")),
                    new \DateTimeImmutable(trim($chunk[3], "'"))
                ));
            }
        }

        return $collection;
    }

    /**
     * Realiza una petición
     *
     * @param string $path Ruta
     * @param bool $returnResponse Indica que debe devolverse el contenido de la respuesta
     * @return string|null
     */
    private function doRequest(string $path, bool $returnResponse = true): ?string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('%s/%s', $this->url, $path));
        curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $this->user, $this->pass));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        if (!$returnResponse) {
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        }

        $error = null;
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }
        curl_close($ch);

        if ($error) {
            if (!$returnResponse && stripos($error, 'Operation timed out after') === false) {
                throw new \RuntimeException($error);
            }
        }

        return $returnResponse ? $response : null;
    }

    /**
     * Obtiene el contenido que contiene las propiedades del dispositivo y las grabaciones
     *
     * @param string $html Documento HTML
     * @return string
     */
    private function getRelevantContent($html): string
    {
        $start = strpos($html, 'var sdcardDetected');
        $end = strpos($html, 'function initTranslation');

        return trim(substr($html, $start, $end - $start));
    }

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
