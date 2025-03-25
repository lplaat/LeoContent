<?php

use Src\App\Models\MediaDirectory;

include '../../config.php';

$sidebarType = 'dashboard';
include '../../components/header.php';

$mediaDirectory = MediaDirectory::find($_GET['id'] ?? null);
?>

<main class="p-2">
    <div class="card">
        <div class="card-body">
            <?php if($mediaDirectory == null) { ?>
                <h2>New directory</h2>

                <form action="/api/dashboard/edit_directory.php" method="POST" class="ajax-request ps-1">
                    <input type="hidden" name="action" value="createMediaDirectory">

                    <div class="row mt-3">
                        <div class="col-2 align-content-center">
                            <label for="path">Path</label>
                        </div>
                        <div class="col-10">
                            <input type="text" name="path" id="path" class="form-control">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-2 align-content-center">
                            <label for="type">Type</label>
                        </div>
                        <div class="col-10">
                            <select id="type" name="type" class="form-control">
                                <option value="movies">Movies</option>
                                <option value="shows">Shows</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-success">Create</button>
                    </div>
                </form>
            <?php } else { ?>
                <div class="d-flex">
                    <h2>Content Directory - <?= $mediaDirectory->path ?></h2>

                    <?php
                    $job = $mediaDirectory->UnfinishedJob;
                    if($job != null) { ?>
                        <div class="ms-auto text-center rounded p-1 d-flex align-items-center bg-<?= $job->ColorStatus ?>">
                            <b class="text-white"><?= $job->TextStatus ?></b>
                        </div>
                    <?php } else { ?>
                        <form action="/api/dashboard/edit_directory.php" method="POST" class="ms-auto ajax-request">
                            <input type="hidden" name="action" value="reScanDirectory">
                            <input type="hidden" name="id" value="<?= $mediaDirectory->id ?>">
                            <button type="submit" class="btn btn-secondary">Rescan</button>
                        </form>
                    <?php } ?>
                </div>

                <hr>

                <div class="p-2">
                    <h3 class="mb-2">Media</h3>
                    <table id="medias" class="table table-striped">
                        <thead>

                        </thead>
                    </table>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function(event) {
                        createDataTable('medias', ['id', 'filename', 'status', 'action'], '/api/dashboard/datatable.php', 'getMedia', {
                            'media_directory_id': <?= $_GET['id'] ?>
                        });
                    });
                </script>
            <?php } ?>
        </div>
    </div>
</main>

<?php
include '../../components/footer.php';