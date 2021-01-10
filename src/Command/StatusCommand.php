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
            $device = $ipcam->getDevice();

            $rows = [
                ['Total Space', 'Free Space', 'Total Files', 'Pages', 'Current State'],
                [
                    $device->getTotalSpace() . ' MB',
                    $device->getFreeSpace() . ' MB',
                    (string)$device->getTotalRecordings(),
                    (string)$device->getTotalPages(),
                    $device->isRunning() ? 'Recording' : 'Idle',
                ],
            ];

            (new TableHelper($io))->output($rows);

            return self::CODE_SUCCESS;
        } catch (\Exception $e) {
            $io->err('ERROR: Se produjo un error al intentar obtener las propiedades del dispositivo.');
        } finally {
            return self::CODE_ERROR;
        }
    }
}
