<?php

include '../../config.php';

$sidebarType = 'dashboard';
include '../../components/header.php';
?>

<main class="p-2">
    <div class="card">
        <div class="card-body">
            <div class="d-flex">
                <h2>Jobs</h2>
            </div>

            <table id="jobs" class="table table-striped">
                <thead>

                </thead>
            </table>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        createDataTable('jobs', ['id', 'type', 'status', 'created-at'], '/api/dashboard/datatable.php', 'getJobs');
    });
</script>


<?php
include '../../components/footer.php';