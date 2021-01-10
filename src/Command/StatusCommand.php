<?php
declare(strict_types=1);

namespace App\Command;

use App\Console\Helper\TableHelper;
use App\Library\IPCamFactory;
use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\ConsoleIo;

/**
 * StatusCommand
 */
class StatusCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        try {
            $ipcam = IPCamFactory::create();

            $rows = [
                ['Total Space', 'Free Space', 'Total Files', 'Pages', 'Current State'],
                [
                    $ipcam->getTotalSpace() . ' MB',
                    $ipcam->getFreeSpace() . ' MB',
                    (string)$ipcam->getTotalRecordings(),
                    (string)$ipcam->getTotalPages(),
                    $ipcam->isRunning() ? 'Recording' : 'Idle',
                ],
            ];

            (new TableHelper($io))->output($rows);

            return self::CODE_SUCCESS;
        } catch (\Exception $e) {
            $io->error('ERROR: Se produjo un error al intentar obtener las propiedades del dispositivo.');
        } finally {
            return self::CODE_ERROR;
        }
    }
}
