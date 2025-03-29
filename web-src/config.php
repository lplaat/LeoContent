<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Src\App\Controllers\AuthController;

const ROOT = '/var/www/html/';

require ROOT . '/vendor/autoload.php';

$config = parse_ini_file(ROOT . '.env');

$capsule = new Capsule();
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $config['MYSQL_HOST'],
    'database'  => $config['MYSQL_DATABASE'],
    'username'  => $config['MYSQL_USER'],
    'password'  => $config['MYSQL_PASSWORD'],
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

new AuthController();
