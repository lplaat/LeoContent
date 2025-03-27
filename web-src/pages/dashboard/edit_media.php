<?php

use Src\App\Controllers\VideoController;

use Src\App\Models\Media;

include '../../config.php';

$sidebarType = 'dashboard';
include '../../components/header.php';

$media = Media::find($_GET['id'] ?? null);
if($media == null){
    header('location: directories.php');
    die();
}

$pathParts = pathinfo($media->path);
$nameData = VideoController::ExtraNameData($pathParts['filename'], 2) ?? [];
?>

<div class="modal fade" id="contentFinder" tabindex="-1" aria-labelledby="contentFinder" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Manual Content Finder - <small style="font-size: 16px;"><?= $pathParts['filename'] ?></small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if($media->MediaDirectory->type == 2) { ?>
                    <form id="search-form">
                        <input type="hidden" id="searchAction" name="action" value="manualEpisodeSearch">
                        <div class="row mt-3">
                            <div class="col-2 align-content-center">
                                <label>Show Name</label>
                            </div>
                            <div class="col-10">
                                <input type="text" name="show_name" value="<?= $nameData['show_name'] ?? '' ?>" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-2 align-content-center">
                                <label>Season</label>
                            </div>
                            <div class="col-10">
                                <input type="number" name="season_number" value="<?= $nameData['season'] ?? '' ?>" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-2 align-content-center">
                                <label>Episode</label>
                            </div>
                            <div class="col-10">
                                <input type="number" name="episode_number" value="<?= $nameData['episode'] ?? '' ?>" class="form-control">
                            </div>
                        </div>
                    </form>

                    <table id="searchResults" class="table table-striped">
                        <thead>

                        </thead>
                    </table>
                <?php } ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="contentSearch()">Search</button>
            </div>
        </div>
    </div>
</div>

<?php if($media->MediaDirectory->type == 2) { ?>
    <script>
        let table = null
        function contentSearch() {
            if(table !== null) {
                table.destroy(); 
            }

            table = createDataTable('searchResults', ['show', 'name', 'episode', 'season', 'action'], '/api/dashboard/edit_media.php?' + $('#search-form').serialize(), $('#searchAction').val());
        }
    </script>
<?php } ?>

<script>
    function newContent(contentId) {
        $('#contentFinder').modal('hide')
        $('#contentId').val(contentId);
        $('#mediaSaveForm').submit();

        if(table !== null) {
            table.destroy(); 
        }
    }
</script>

<main class="p-2">
    <div class="card">
        <div class="card-body">
            <h2>Media - <?= basename($media->path) ?> </h2>

            <form action="/api/dashboard/edit_media.php" method="POST" id="mediaSaveForm" class="ajax-request ps-1">
                <input type="hidden" name="action" value="saveMedia">
                <input type="hidden" name="id" value="<?= $media->id ?>">
                <input type="hidden" name="content_id" id="contentId" value="<?= $media->content_id ?>">

                <div class="row mt-3">
                    <div class="col-2 align-content-center">
                        <label>Path</label>
                    </div>
                    <div class="col-10">
                        <input type="text" name="path" value="<?= $media->path ?>" class="form-control">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-2 align-content-center">
                        <label>Linked Content</label>
                    </div>
                    <div class="col-10">
                        <?php if($media->content_id !== null) { ?>
                            <a href="/dashboard/edit_content.php?id=<?= $media->Content->id ?>"><?= $media->Content->title ?></a> - 
                        <?php } ?>

                        <a href="#" data-bs-toggle="modal" data-bs-target="#contentFinder">Manual find content</a>
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
