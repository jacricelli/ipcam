<?php
declare(strict_types=1);

namespace IPCam\Command;

use IPCam\IPCamFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * RebootCommand
 */
class RebootCommand extends Command
{
    /**
     * Nombre del comando
     *
     * @var string
     */
    protected static $defaultName = 'reboot';

    /**
     * Configura el comando
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Reinicia el dispositivo')
            ->setHelp('Este comando reinicia el dispositivo.');
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
            $question = new ConfirmationQuestion('¿Confirma el reinicio del dispositivo? [y/N] ', false);

            if ($helper->ask($input, $output, $question)) {
                IPCamFactory::create()->reboot();
                $output->writeln('<info>Se ha enviado el comando de reinicio al dispositivo.</info>');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>ERROR: ' . $e->getMessage() . '</error>');
        } finally {
            return Command::FAILURE;
        }
    }
}
