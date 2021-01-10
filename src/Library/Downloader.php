<?php
declare(strict_types=1);

namespace App\Library;

use Cake\Console\ConsoleIo;
use pq\Exception\RuntimeException;

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
     */
    public function __construct(string $url, string $path, string $user, string $pass)
    {
        $this->url = $url;
        $this->path = $path;
        $this->credentials = $user . ':' . $pass;
    }

    /**
     * Establece la instancia de ConsoleIo
     *
     * @param \Cake\Console\ConsoleIo|null $io Instancia de ConsoleIo
     * @return void
     */
    public function setConsoleIo(?ConsoleIo $io = null): void
    {
        $this->io = $io;
    }

    /**
     * Descarga una colección de grabaciones
     *
     * @param \App\Library\RecordingCollection $recordings Colección de grabaciones
     * @return void
     */
    public function downloadCollection(RecordingCollection $recordings): void
    {
        if (!$recordings->count()) {
            throw new RuntimeException('La colección de grabaciones está vacía.');
        }

        foreach ($recordings as $recording) {
            if (!$this->download($recording)) {
                throw new RuntimeException(
                    sprintf('Se produjo un error al descargar la grabación %s.', $recording->getFilename())
                );
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
        $file = $this->path . DS . $recording->getStartDate()->format('Y-m-d h-i') . '.avi';
        if (file_exists($file)) {
            return true;
        }

        $name = $recording->getFilename();
        $url = sprintf('%s/sd/%s', $this->url, urlencode($name));
        $tempfile = $file . '.tmp';

        $fp = fopen($tempfile, 'w+');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (!$this->io) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, true);
        } else {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function (
                $resource,
                $downloadSize,
                $downloadedSize
            ) use ($name) {
                if ($downloadSize) {
                    $this->io->overwrite(
                        sprintf('[%d%%] %s', round($downloadedSize * 100 / $downloadSize), $name),
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
}
