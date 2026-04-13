<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;

$kernel = $app->make(Kernel::class);

$commands = $kernel->all();
$commandNames = [];

foreach ($commands as $name => $command) {
    $commandNames[] = $name;
}

echo "<pre>";
print_r($commandNames);