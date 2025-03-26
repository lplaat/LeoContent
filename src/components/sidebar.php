<?php

use Src\App\Models\User;

$user = User::find($_SESSION['userId']);

$type = isset($sidebarType) ? $sidebarType : 'default';

$transparentNav = isset($transparentNav) && $transparentNav;

if($type == 'default') { ?>
    <nav class="navbar navbar-expand-lg <?= $transparentNav ? 'position-absolute w-100' : ''?>" style="z-index: 1;">
        <div class="container-fluid">
            <a class="navbar-brand" href="/home">LeoContent</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="ms-auto navbar-nav">
                    <li class="nav-item px-2">
                        <a class="nav-link" href="/search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </a>
                    </li>
                    <?php if($user->is_admin) { ?>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="/dashboard/directories.php">
                                <i class="fa-solid fa-bars"></i>
                            </a>
                        </li>
                    <?php } ?>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="/login?logout=true">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php } else if($type == 'dashboard') { ?>
    <div class="sidebar d-flex flex-column">
        <div>
            <h3 class="px-4">Dashboard</h3>
            <a href="/dashboard/directories.php"><i class="fas fa-folder"></i> Directories</a>
            <a href="/dashboard/content.php"><i class="fa-solid fa-film"></i> Contents</a>
            <a href="/dashboard/jobs.php"><i class="fas fa-server"></i> Jobs</a>

            <a><i class="fas fa-user"></i> Users</a>
        </div>

        <div class="mt-auto">
            <a href="/home"><i class="fas fa-sign-out-alt"></i> Go Back</a>
        </div>
    </div>
<?php } ?>
