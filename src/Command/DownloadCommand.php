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
            $ipcam = IPCamFactory::create($io);
            $page = (int)$args->getArgument('page');
            $skip = $this->parseSkipArgument($args->getArgument('skip'), $ipcam->getRecordingsPerPage());

            if ($page) {
                $ipcam->downloadRecordings($page, $skip);
            } else {
                $total = $ipcam->getTotalPages();
                for ($page = $total; $page >= 1; $page--) {
                    $io->info('Descargando página ' . $page . '...');
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
            ])
            ->addArgument('skip', [
                'required' => false,
                'help' => 'Skip recording',
            ]);

        return parent::buildOptionParser($parser);
    }

    /**
     * Determina los números de grabaciones que no deben descargarse
     *
     * @param string $argument Argumento Argumento
     * @param int $limit Límite de valores a devolver
     * @return array
     */
    private function parseSkipArgument(string $argument, int $limit = 0): array
    {
        $values = explode(',', $argument);
        $result = [];
        foreach ($values as $value) {
            if (strpos($value, '-')) {
                $range = explode('-', $value);
                $result = array_merge($result, range(min($range), max($range)));
            } else {
                $result[] = (int)$value;
            }
        }
        $result = array_filter(array_unique($result));

        return $limit ? array_slice($result, 0, $limit) : $result;
    }
}
