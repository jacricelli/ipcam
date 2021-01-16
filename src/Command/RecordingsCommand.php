<?php
declare(strict_types=1);

namespace IPCam\Command;

use IPCam\IPCamFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RecordingsCommand
 */
class RecordingsCommand extends Command
{
    /**
     * Nombre del comando
     *
     * @var string
     */
    protected static $defaultName = 'recordings';

    /**
     * Configura el comando
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Lista las grabaciones')
            ->setHelp('Este comando lista todas las grabaciones.');
    }

    /**
     * Ejecuta el comando
     *
     * @param InputInterface $input Entrada
     * @param OutputInterface $output Salida
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $recordings = IPCamFactory::create()->getRecordings();

            if (!$recordings->count()) {
                $output->writeln('<info>No se han encontrado grabaciones.</info>');
            } else {
                $table = new Table($output);
                $table->setHeaders(['#', 'Filename', 'Size', 'Start Date', 'End Date']);
                foreach ($recordings as $index => $recording) {
                    $table->addRow([
                        (string)++$index,
                        $recording->getFileName(),
                        $recording->getFileSize() . ' kB',
                        $recording->getStartDate()->format('d/m/y H:i'),
                        $recording->getEndDate()->format('d/m/y H:i')
                    ]);
                }
                $table->render();
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>ERROR: ' . $e->getMessage() . '</error>');
        } finally {
            return Command::FAILURE;
        }
    }
}
