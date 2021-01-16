<?php
declare(strict_types=1);

namespace IPCam\Command;

use IPCam\IPCamFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * StatusCommand
 */
class StatusCommand extends Command
{
    /**
     * Nombre del comando
     *
     * @var string
     */
    protected static $defaultName = 'status';

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
            $device = IPCamFactory::create()->getDevice();

            $table = new Table($output);
            $table
                ->setHeaders(['Total Space', 'Free Space', 'Total Files', 'Pages', 'Current State'])
                ->setRows([
                    [
                        $device->getTotalSpace() . ' MB',
                        $device->getFreeSpace() . ' MB',
                        (string)$device->getTotalRecordings(),
                        (string)$device->getTotalPages(),
                        $device->isRecording() ? 'Recording' : 'Idle',
                    ],
                ]);
            $table->render();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>ERROR: ' . $e->getMessage() . '</error>');
        } finally {
            return Command::FAILURE;
        }
    }
}
