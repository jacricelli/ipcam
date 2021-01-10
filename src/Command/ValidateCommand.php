<?php
declare(strict_types=1);

namespace App\Command;

use App\Library\DownloaderFactory;
use App\Library\IPCamFactory;
use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\ConsoleIo;

/**
 * ValidateCommand
 */
class ValidateCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        try {
            $ipcam = IPCamFactory::create($io);
            $downloader = DownloaderFactory::create($io);
            $total = $ipcam->getTotalPages();
            for ($page = $total; $page >= 1; $page--) {
                $downloader->validateCollection($ipcam->getRecordings($page));
            }

            return self::CODE_SUCCESS;
        } catch (\Exception $e) {
            $io->error('ERROR: Se produjo un error al intentar obtener las propiedades del dispositivo.');
        } finally {
            return self::CODE_ERROR;
        }
    }
}
