<?php
declare(strict_types=1);

namespace App\Command;

use App\Library\IPCamFactory;
use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * DownloadCommand
 */
class DownloadCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        try {
            $page = (int)$args->getArgument('page');
            $ipcam = IPCamFactory::create($io);
            if ($page) {
                $ipcam->downloadRecordings($page);
            } else {
                $total = $ipcam->getTotalPages();
                for ($page = $total; $page >= 1; $page--) {
                    $ipcam->downloadRecordings($page);
                }
            }

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
                'required' => false,
                'help' => 'Page number',
            ]);

        return parent::buildOptionParser($parser);
    }
}
