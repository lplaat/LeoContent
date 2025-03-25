<?php

use Src\App\Models\User;

include '../config.php';

$status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = User::where('username', $_POST['username'])->first();
    if(password_verify($_POST['password'], $user->password)) {
        $_SESSION['userId'] = $user->id;
        header('Refresh: 1; url=/home');
        $status = 'success';
    } else {
        $status = 'failed';
    }
}

if(isset($_GET['logout'])) {
    $status = 'logout';
    header('Refresh: 1; url=/login');
    session_destroy();
}

include '../components/header.php';

?>

<div class="container d-flex justify-content-center align-items-center" style="height: 75vh;">
    <div class="card" style="min-width: 50vw;">
        <div class="card-body">
            <form action="/login" method="post">
                <h3 class="mb-2"><b>Logging into LeoContent</b></h3>

                <div class="form-group mb-2">
                    <label for="exampleInputEmail1">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username">
                </div>

                <div class="form-group mb-2">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>

                <?php if($status == 'success') { ?>
                    <span class="text-success">You successfully logged in!</span>
                <?php } else if($status == 'failed') { ?>
                    <span class="text-danger">Your password or username is wrong</span>
                <?php } else if($status == 'logout') { ?>
                    <span class="text-danger">You are being logged out</span>
                <?php } ?>

                <div class="mt-2 d-flex">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include '../components/footer.php';