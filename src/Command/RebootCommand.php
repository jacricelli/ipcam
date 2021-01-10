<?php
declare(strict_types=1);

namespace App\Command;

use App\Library\IPCamFactory;
use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\ConsoleIo;

/**
 * RebootCommand
 */
class RebootCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $result = strtolower($io->ask('¿Confirma el reinicio del dispositivo? [Y/n]', 'n'));
        if ($result === 'y') {
            $ipcam = IPCamFactory::create();
            $ipcam->reboot();
        }

        return self::CODE_SUCCESS;
    }
}
