<?php
declare(strict_types=1);

namespace App;

use App\Command\RecordingsCommand;
use App\Command\StatusCommand;
use Cake\Console\CommandCollection;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Core\ConsoleApplicationInterface;

/**
 * Aplicación
 */
class Application implements ConsoleApplicationInterface
{
    /**
     * Arranque
     *
     * @return void
     */
    public function bootstrap(): void
    {
        Configure::config('default', new PhpConfig(dirname(__DIR__) . DS . 'config' . DS));
        Configure::load('ipcam', 'default', false);
    }

    /**
     * Define los comandos de consola para una aplicación.
     *
     * @param \Cake\Console\CommandCollection $commands La colección a la que se le agregan los comandos.
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('status', StatusCommand::class);
        $commands->add('recordings', RecordingsCommand::class);

        return $commands;
    }
}
