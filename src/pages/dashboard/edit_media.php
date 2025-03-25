<?php

use Src\App\Models\Media;

include '../../config.php';

$sidebarType = 'dashboard';
include '../../components/header.php';

$media = Media::find($_GET['id'] ?? null);
if($media == null){
    header('location: directories.php');
    die();
}
?>

<main class="p-2">
    <div class="card">
        <div class="card-body">
            <h2>Media - <?= basename($media->path) ?> </h2>

            <form action="/api/dashboard/edit_media.php" method="POST" class="ajax-request ps-1">
                <input type="hidden" name="action" value="saveMedia">
                <input type="hidden" name="id" value="<?= $media->id ?>">

                <div class="row mt-3">
                    <div class="col-2 align-content-center">
                        <label>Path</label>
                    </div>
                    <div class="col-10">
                        <input type="text" name="path" value="<?= $media->path ?>" class="form-control">
                    </div>
                </div>

                <?php if($media->content_id !== null) { 
                    ?>
                    <div class="row mt-3">
                        <div class="col-2 align-content-center">
                            <label>Linked Content</label>
                        </div>
                        <div class="col-10">
                            <a href="/dashboard/edit_content.php?id=<?= $media->Content->id ?>"><?= $media->Content->title ?></a>
                        </div>
                    </div>
                <?php } ?>

                <div class="d-flex mt-1">
                    <button type="submit" class="ms-auto btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
include '../../components/footer.php';
