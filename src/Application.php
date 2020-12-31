<?php
namespace App;

use Cake\Console\CommandCollection;
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
    }

    /**
     * Define los comandos de consola para una aplicación.
     *
     * @param \Cake\Console\CommandCollection $commands La colección a la que se le agregan los comandos.
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        return $commands;
    }
}
