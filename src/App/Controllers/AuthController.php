<?php

namespace Src\App\Controllers;

class AuthController
{
    public function __construct()
    {
        session_start();

        if (!isset($_SESSION['userId']) && basename($_SERVER['PHP_SELF']) != 'login.php' && php_sapi_name() !== 'cli') {
            header('Location: /login');
            exit();
        }
    }
}