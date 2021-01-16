<?php
/** @noinspection PhpMissingFieldTypeInspection */
declare(strict_types=1);

namespace IPCam\Command;

use IPCam\IPCamFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * FormatCommand
 */
class FormatCommand extends Command
{
    /**
     * Nombre del comando
     *
     * @var string
     */
    protected static $defaultName = 'format';

    /**
     * Configura el comando
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Formatea el almacenamiento')
            ->setHelp('Este comando formatea la tarjeta de memoria del dispositivo.');
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
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('¿Confirma el formateo del almacenamiento? [y/N] ', false);

            if ($helper->ask($input, $output, $question)) {
                IPCamFactory::create()->format();
                $output->writeln('<info>Se ha enviado el comando de formateo al dispositivo.</info>');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>ERROR: ' . $e->getMessage() . '</error>');
        } finally {
            return Command::FAILURE;
        }
    }
}
