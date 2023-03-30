<?php
require __DIR__.'/../../vendor/autoload.php';

use App\Main;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

$main = new Main();
$main->run();
