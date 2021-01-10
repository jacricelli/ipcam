<?php
declare(strict_types=1);

namespace App\Command;

use App\Library\IPCamFactory;
use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * DeleteCommand
 */
class DeleteCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $result = strtolower($io->ask('¿Confirma la eliminación de las grabaciones? [Y/n]', 'n'));
        if ($result !== 'y') {
            return self::CODE_SUCCESS;
        }

        try {
            $ipcam = IPCamFactory::create($io);
            $ipcam->deleteRecordings((int)$args->getArgument('page'));

            return self::CODE_SUCCESS;
        } catch (\Exception $e) {
            $io->error('ERROR: ' . $e->getMessage());
        } finally {
            return self::CODE_ERROR;
        }
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->addArgument('page', [
                'required' => true,
                'help' => 'Page number',
            ]);

        return parent::buildOptionParser($parser);
    }
}
