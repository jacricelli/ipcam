<?php
/** @noinspection PhpUnusedFieldDefaultValueInspection */
declare(strict_types=1);

namespace App\Library;

use Cake\Console\ConsoleIo;
use RuntimeException;

/**
 * Downloader
 */
class Downloader
{
    /**
     * URL
     *
     * @var string
     */
    private string $url;

    /**
     * Ruta donde guardar los archivos
     *
     * @var string
     */
    private string $path;

    /**
     * Credenciales
     *
     * @var string
     */
    private string $credentials;

    /**
     * Instancia de ConsoleIo
     *
     * @var \Cake\Console\ConsoleIo|null
     */
    private ?ConsoleIo $io = null;

    /**
     * Constructor
     *
     * @param string $url URL
     * @param string $path Ruta donde guardar los archivos
     * @param string $user Usuario
     * @param string $pass Contraseña
     * @param \Cake\Console\ConsoleIo|null $io Instancia de ConsoleIo
     */
    public function __construct(string $url, string $path, string $user, string $pass, ?ConsoleIo $io = null)
    {
        $this->url = $url;
        $this->path = $path;
        $this->credentials = $user . ':' . $pass;
        $this->io = $io;
    }

    /**
     * Descarga una colección de grabaciones
     *
     * @param \App\Library\RecordingCollection $recordings Colección de grabaciones
     * @param array $skip El número de una o más grabaciones que no deben descargarse
     * @return void
     */
    public function downloadCollection(RecordingCollection $recordings, array $skip = []): void
    {
        if (!$recordings->count()) {
            throw new RuntimeException('La colección de grabaciones está vacía.');
        }

        foreach ($recordings as $index => $recording) {
            if (!in_array(++$index, $skip)) {
                if (!$this->download($recording)) {
                    throw new RuntimeException(
                        sprintf('Se produjo un error al descargar la grabación %s.', $recording->getFilename())
                    );
                }
            } else {
                $this->io->info('Omitiendo la descarga de la grabación ' . $recording->getFilename());
            }
        }
    }

    /**
     * Valida las descargas de una colección de grabaciones
     *
     * @param \App\Library\RecordingCollection $recordings Colección de grabaciones
     * @return void
     */
    public function validateCollection(RecordingCollection $recordings): void
    {
        if (!$recordings->count()) {
            throw new RuntimeException('La colección de grabaciones está vacía.');
        }

        foreach ($recordings as $recording) {
            $path = $this->getPath($recording);
            $file = $this->getFileName($recording);

            if (!file_exists($path . DS . $file)) {
                $this->io->info('No se ha encontrado la descarga de la grabación ' . basename($file));
            }
        }
    }

    /**
     * Descarga una grabación
     *
     * @param \App\Library\RecordingInterface $recording Grabación
     * @return bool
     * @noinspection PhpUnusedParameterInspection
     */
    private function download(RecordingInterface $recording): bool
    {
        $path = $this->getPath($recording);
        if (!file_exists($path)) {
            if (!mkdir($path)) {
                throw new \RuntimeException('No se puede crear el directorio ' . $path);
            }
        }

        $file = $path . DS . $this->getFileName($recording);
        if (file_exists($file)) {
            return true;
        }

        $tempfile = $path . DS . $this->getTempFileName($recording);
        $fp = fopen($tempfile, 'w+');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getFileUrl($recording));
        curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (!$this->io) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, true);
        } else {
            $filename = basename($file);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function (
                $resource,
                $downloadSize,
                $downloadedSize
            ) use ($filename) {
                if ($downloadSize) {
                    $this->io->overwrite(
                        sprintf('[%d%%] %s', round($downloadedSize * 100 / $downloadSize), $filename),
                        0
                    );
                }
            });
        }

        $result = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        if ($result) {
            rename($tempfile, $file);
        }

        if ($this->io) {
            $this->io->out();
        }

        return $result;
    }

    /**
     * Obtiene la ruta de acceso donde guardar la grabación
     *
     * @param \App\Library\RecordingInterface $recording Grabación
     * @return string
     */
    private function getPath(RecordingInterface $recording): string
    {
        return $this->path . DS . $recording->getStartDate()->format('Y-m');
    }

    /**
     * Obtiene el nombre del archivo de la grabación
     *
     * @param \App\Library\RecordingInterface $recording Grabación
     * @return string
     */
    private function getFileName(RecordingInterface $recording): string
    {
        return $recording->getStartDate()->format('Y-m-d_H-i-s') . '.avi';
    }

    /**
     * Obtiene el nombre del archivo temporal de la grabación
     *
     * @param \App\Library\RecordingInterface $recording Grabación
     * @return string
     */
    private function getTempFileName(RecordingInterface $recording): string
    {
        return $this->getFileName($recording) . '.tmp';
    }

    /**
     * Obtiene la URL de la grabación
     *
     * @param \App\Library\RecordingInterface $recording Grabación
     * @return string
     */
    private function getFileUrl(RecordingInterface $recording): string
    {
        return sprintf('%s/sd/%s', $this->url, urlencode($recording->getFilename()));
    }
}
