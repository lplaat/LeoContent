<?php

include '../../config.php';

$sidebarType = 'dashboard';
include '../../components/header.php';
?>

<main class="p-2">
    <div class="card">
        <div class="card-body">
            <div class="d-flex">
                <h2>Directories</h2>
                <div class="ms-auto text-center rounded p-1 d-flex align-items-center">
                    <a class="btn btn-success" href="edit_directory.php"><i class="fa-solid fa-plus"></i></a>
                </div>
            </div>

            <table id="directories" class="table table-striped">
                <thead>

                </thead>
            </table>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        createDataTable('directories', ['id', 'path', 'type', 'media-amount', 'action'], '/api/dashboard/datatable.php', 'getDirectories');
    });
</script>


<?php
include '../../components/footer.php';