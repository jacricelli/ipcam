#!/usr/bin/php -q
<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application;
use Cake\Console\CommandRunner;

$runner = new CommandRunner(new Application(), 'tool');
exit($runner->run($argv));
