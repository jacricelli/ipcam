<?php
/** @noinspection PhpPureAttributeCanBeAddedInspection */
declare(strict_types=1);

namespace IPCam;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param string $savedir Directorio donde se guardan las descargas
     */
    public function __construct(
        private string $url,
        private string $user,
        private string $pass,
        private string $savedir
    )
    {
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
     * Descarga las grabaciones
     *
     * @param OutputInterface|null $output Salida
     * @param array $skip Lista de archivos que no deben descargarse
     * @return void
     * @throws \Exception
     */
    public function downloadRecordings(?OutputInterface $output, array $skip = []): void
    {
        $recordings = $this->getRecordings();
        $total = $recordings->count();
        if (!$total) {
            throw new \RuntimeException('No se han encontrado grabaciones.');
        }

        $output->writeln('<info>Comenzando a descargar las grabaciones...</info>');

        $skip = array_flip($skip);
        foreach ($recordings as $index => $recording) {
            if (!isset($skip[$recording->getFileName()])) {
                ProgressBar::setFormatDefinition('custom', "- [" . ++$index . "/$total] %message% [%percent:3s%%]");
                if (!$this->downloadFile($recording, $output)) {
                    throw new \RuntimeException(
                        'Se produjo un error al descargar la grabación ' . $recording->getFileName()
                    );
                }
            } else {
                $output->writeln(
                    '<info>- Omitiendo la descarga de la grabación ' . $recording->getFileName() . '</info>'
                );
            }
        }

        $output->writeln(PHP_EOL . '<info>Se han descargado todas las grabaciones.</info>');
    }

    /**
     * Reinicia el dispositivo
     *
     * @return void
     */
    public function reboot(): void
    {
        $this->doRequest('media/?action=cmd&code=255&value=255', false);
    }

    /**
     * Formatea el almacenamiento del dispositivo
     *
     * @return void
     * @throws \RuntimeException
     */
    public function format(): void
    {
        $content = $this->doRequest('rec_action.cgi?op=fmt');
        if (strtolower(trim($content)) !== 'ok') {
            throw new \RuntimeException('Se produjo un error interno al formatear el almacenamiento.');
        }
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

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            if ($returnResponse || $errno !== CURLE_OPERATION_TIMEDOUT) {
                throw new \RuntimeException($error, $errno);
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
    private function getRelevantContent(string $html): string
    {
        $start = strpos($html, 'var sdcardDetected');
        $end = strpos($html, 'function initTranslation');

        return trim(substr($html, $start, $end - $start));
    }

    /**
     * Descarga una grabación
     *
     * @param Recording $recording Grabación
     * @param OutputInterface|null $output Salida
     * @return bool
     * @noinspection PhpUnusedParameterInspection
     */
    private function downloadFile(Recording $recording, ?OutputInterface $output): bool
    {
        $path = $this->getPath($recording);
        if (!file_exists($path)) {
            if (!mkdir($path)) {
                throw new \RuntimeException('No se puede crear el directorio ' . $path);
            }
        }

        $file = $path . DIRECTORY_SEPARATOR . $this->getFileName($recording);
        if (file_exists($file)) {
            return true;
        }

        $tempfile = $path . DIRECTORY_SEPARATOR . $this->getTempFileName($recording);
        $fp = fopen($tempfile, 'w+');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getFileUrl($recording));
        curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $this->user, $this->pass));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (!$output) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, true);
        } else {
            $progressBar = new ProgressBar($output, $recording->getFileSize() * 1024);
            $progressBar->setFormat('custom');
            $progressBar->setMessage(basename($file));

            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function (
                $resource,
                $downloadSize,
                $downloadedSize
            ) use ($progressBar) {
                $progressBar->setProgress($downloadedSize);
            });
        }

        $result = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        if ($output) {
            $progressBar->finish();
            $output->writeln('');
        }

        if ($result) {
            rename($tempfile, $file);
        }

        return $result;
    }

    /**
     * Obtiene la ruta de acceso donde guardar la grabación
     *
     * @param \IPCam\Recording $recording Grabación
     * @return string
     */
    private function getPath(Recording $recording): string
    {
        return $this->savedir . DIRECTORY_SEPARATOR . $recording->getStartDate()->format('Y-m');
    }

    /**
     * Obtiene el nombre del archivo de la grabación
     *
     * @param \IPCam\Recording $recording Grabación
     * @return string
     */
    private function getFileName(Recording $recording): string
    {
        return $recording->getStartDate()->format('Y-m-d_H-i-s') . '.avi';
    }

    /**
     * Obtiene el nombre del archivo temporal de la grabación
     *
     * @param \IPCam\Recording $recording Grabación
     * @return string
     */
    private function getTempFileName(Recording $recording): string
    {
        return $this->getFileName($recording) . '.tmp';
    }

    /**
     * Obtiene la URL de la grabación
     *
     * @param \IPCam\Recording $recording Grabación
     * @return string
     */
    private function getFileUrl(Recording $recording): string
    {
        return sprintf('%s/sd/%s', $this->url, urlencode($recording->getFilename()));
    }
}
