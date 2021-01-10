<?php
declare(strict_types=1);

namespace App\Command;

use App\Library\IPCamFactory;
use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\ConsoleIo;

/**
 * FormatCommand
 */
class FormatCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $result = strtolower($io->ask('¿Confirma el formateo del almacenamiento? [Y/n]', 'n'));
        if ($result === 'y') {
            $ipcam = IPCamFactory::create();
            $ipcam->format();
        }

        return self::CODE_SUCCESS;
    }
}
