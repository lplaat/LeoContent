<?php

use Src\App\Models\Content;

include '../../config.php';

$sidebarType = 'dashboard';
include '../../components/header.php';

$content = Content::find($_GET['id'] ?? null);
if($content == null){
    header('location: directories.php');
    die();
}
?>

<main class="p-2">
    <div class="card">
        <div class="card-body">
            <h2>Content - <?= basename($content->title) ?> </h2>

            <form action="/api/dashboard/edit_content.php" method="POST" class="ajax-request ps-1">
                <input type="hidden" name="action" value="saveContent">
                <input type="hidden" name="id" value="<?= $content->id ?>">

                <div class="row mt-3">
                    <div class="col-2 align-content-center">
                        <label>Title</label>
                    </div>
                    <div class="col-10">
                        <input type="text" name="title" value="<?= $content->title ?>" class="form-control">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-2 align-content-center">
                        <label>Description</label>
                    </div>
                    <div class="col-10">
                        <textarea name="description" class="form-control"><?= $content->description ?></textarea>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-2 align-content-center">
                        <label>Release date</label>
                    </div>
                    <div class="col-10">
                        <input type="datetime-local" name="release_date" value="<?= $content->release_date ?>" class="form-control">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-2 align-content-center"></div>
                    <div class="col-10">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="adult_only" role="switch" id="adultsOnly" <?= $content->adult_only ? 'checked' : '' ?>>
                            <label class="form-check-label" for="adultsOnly">Adults Only</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex mt-1">
                    <button type="submit" class="ms-auto btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
include '../../components/footer.php';
