<?php
declare(strict_types=1);

namespace App\Command;

use App\Library\DownloaderFactory;
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
            $ipcam = IPCamFactory::create();
            $recordings = $ipcam->getRecordings((int)$args->getArgument('page'));

            $downloader = DownloaderFactory::create();
            $downloader->setConsoleIo($io);
            $downloader->downloadCollection($recordings);

            return self::CODE_SUCCESS;
        } catch (\Exception $e) {
            $io->err('ERROR: ' . $e->getMessage());
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
