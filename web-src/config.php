<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Src\App\Controllers\AuthController;

const ROOT = '/var/www/html/';

require ROOT . '/vendor/autoload.php';

$config = parse_ini_file(ROOT . '.env');

$capsule = new Capsule();
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST') ?: 'db',
    'database'  => getenv('DB_NAME') ?: 'leoContent',
    'username'  => getenv('DB_USERNAME') ?: 'root',
    'password'  => getenv('DB_PASSWORD') ?: 'root',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

new AuthController();
