<?php

use Src\App\Models\Content;

include '../config.php';

$content = Content::find($_GET['id']);
if($content == null) {
    header('location: /home');
    die();
}

include '../components/header.php';

?>

<style>
    body {
        overflow-y: hidden;
    }

    .content-detail {
        min-height: calc(100vh - 50px);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
    }

    .content-overlay {
        background: rgba(0,0,0,0.7);
        min-height: calc(100vh - 50px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .content-card {
        background: rgba(0,0,0,0.4);
        border-radius: 15px;
        max-width: 1000px;
        width: 100%;
    }

    .poster-img {
        max-height: 500px;
        object-fit: cover;
        border-radius: 15px;
    }
</style>

<div class="content-detail" style="background-image: url('<?= $content->backdrop ? $content->backdrop->Url() : '' ?>')">
    <div class="content-overlay">
        <div class="content-card container">
            <div class="row g-4 p-4">
                <div class="col-md-4">
                    <?php if($content->poster): ?>
                        <img src="<?= $content->poster->Url() ?>" alt="<?= htmlspecialchars($content->title) ?> Poster" class="img-fluid poster-img shadow-lg">
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <h1 class="display-4 mb-3"><?= htmlspecialchars($content->title) ?></h1>
                    
                    <div class="mb-4">
                        <span class="badge bg-primary me-2">
                            <i class="fas fa-film me-2"></i><?= $content->getTextTypeAttribute() ?>
                        </span>
                        
                        <?php if($content->adult_only): ?>
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>Adult Content
                            </span>
                        <?php endif; ?>
                    </div>

                    <p class="lead mb-4"><?= htmlspecialchars($content->description) ?></p>

                    <div class="mb-4">
                        <strong>Release Date:</strong> 
                        <?= date('F d, Y', strtotime($content->release_date)) ?>
                    </div>

                    <div class="d-flex gap-3">
                        <button class="btn btn-danger" onclick="likeContent()">
                            <i class="fas fa-heart me-2"></i>Like
                        </button>
                        <button class="btn btn-success" onclick="watchContent()">
                            <i class="fas fa-play me-2"></i>Watch Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../components/footer.php';
