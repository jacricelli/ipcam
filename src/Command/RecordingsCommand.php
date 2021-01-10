<?php
declare(strict_types=1);

namespace App\Command;

use App\Console\Helper\TableHelper;
use App\Library\IPCamFactory;
use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * RecordingsCommand
 */
class RecordingsCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        try {
            $ipcam = IPCamFactory::create();
            $recordings = $ipcam->getRecordings((int)$args->getArgument('page'));

            if (!$recordings->count()) {
                throw new \RuntimeException('La colección de grabaciones está vacía.');
            }

            $rows[] = ['#', 'Filename', 'Size', 'Start Date', 'End Date'];
            foreach ($recordings as $index => $recording) {
                $rows[] = [
                    (string)++$index,
                    $recording->getFilename(),
                    (round($recording->getFileSize() / 1024)) . ' MB',
                    $recording->getStartDate()->format('d/m/y H:i'),
                    $recording->getEndDate()->format('d/m/y H:i'),
                ];
            }

            (new TableHelper($io))->output($rows);

            return self::CODE_SUCCESS;
        } catch (\Exception $e) {
            $io->error('ERROR: Se produjo un error al intentar obtener las grabaciones.');
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
