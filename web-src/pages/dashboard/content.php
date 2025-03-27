<?php

include '../../config.php';

$sidebarType = 'dashboard';
include '../../components/header.php';
?>

<main class="p-2">
    <div class="card">
        <div class="card-body">
            <div class="d-flex">
                <h2>Content</h2>
            </div>

            <table id="content" class="table table-striped">
                <thead>

                </thead>
            </table>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        createDataTable('content', ['id', 'title', 'release-date', 'type', 'status', 'action'], '/api/dashboard/datatable.php', 'getContent');
    });
</script>

<?php
include '../../components/footer.php';