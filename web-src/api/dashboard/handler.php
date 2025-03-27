<?php

use Src\App\Models\User;

include __DIR__ . '/../../config.php';

$user = User::find($_SESSION['userId'])->first();
if($user == null || ($user->is_admin ?? null) != 1){
    die();
}

if(($_REQUEST['action'] ?? null) == null) {
    die();
}

if(!is_callable($_REQUEST['action'])) {
    return;
}

header('Content-Type: application/json;');

$response = $_REQUEST['action']($_REQUEST);
echo json_encode($response);