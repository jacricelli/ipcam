<?php
/** @noinspection PhpMissingFieldTypeInspection */
declare(strict_types=1);

namespace IPCam\Command;

use IPCam\IPCamFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DownloadCommand
 */
class DownloadCommand extends Command
{
    /**
     * Nombre del comando
     *
     * @var string
     */
    protected static $defaultName = 'download';
    /**
     * Configura el comando
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Descarga grabaciones')
            ->setHelp('Este comando descarga todas las grabaciones en la ubicación IPCAM_SAVEDIR.')
            ->addArgument(
                'skip',
                InputArgument::OPTIONAL,
                'Lista de grabaciones separadas por comas que no deben descargarse.'
            );
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
            $skip = array_filter(
                array_unique(
                    explode(',', (string)$input->getArgument('skip'))
                )
            );

            IPCamFactory::create()->downloadRecordings($output, $skip);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>ERROR: ' . $e->getMessage() . '</error>');
        } finally {
            return Command::FAILURE;
        }
    }
}
