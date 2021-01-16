<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$application = new Application('IPCam', '@package_version@');

try {
    $pharRunning = Phar::running(false);
    if ($pharRunning) {
        $pharRunning = dirname($pharRunning);
    }

    (new Dotenv())->loadEnv(($pharRunning ?: __DIR__) . '/.env');
} catch (\Exception $e) {
    echo "ERROR: No se ha encontrado el archivo de configuración o el formato del mismo no es válido.\n";
    exit(1);
}

$application->add(new \IPCam\Command\DownloadCommand());
$application->add(new \IPCam\Command\FormatCommand());
$application->add(new \IPCam\Command\RebootCommand());
$application->add(new \IPCam\Command\RecordingsCommand());
$application->add(new \IPCam\Command\StatusCommand());

try {
    $application->run();
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
