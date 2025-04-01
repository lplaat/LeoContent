<style>
.hero-section {
    position: relative;
    min-height: 80vh;
}

.hero-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.75);
    z-index: 0;
    object-position: top;
}

.hero-content {
    position: relative;
    z-index: 1;
    margin-inline: 6vw;
}

.text-container {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
}

.tag-separator::after {
    content: "•";
    margin: 0 0.5rem;
}

.swiper-pagination-bullet-active {
  opacity: 1 !important;
  background: #fff !important;
}

</style>

<div class="swiper">
    <div class="swiper-wrapper">
        <?php foreach ($contents as $content) { 
            $contentType = $content->type == 1 ? 'movie' : 'show';
            $watchButtonText = $contentType == 'movie' ? 'Play' : 'Play Episode 1';
            ?>
            <section class="swiper-slide hero-section d-flex justify-content-center align-items-center">
                <img src="<?= $content->backdrop ? $content->backdrop->Url() : '' ?>" class="hero-image" alt="<?= htmlspecialchars($content->title) ?> backdrop">

                <div class="hero-content">
                    <!-- Title section -->
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <h1 class="display-4 fw-bold"><?= htmlspecialchars($content->title) ?></h1>
                            
                            <!-- Metadata -->
                            <div class="d-flex flex-wrap align-items-center mb-3">
                                <span class="text-white fw-bold"><?= date('Y', strtotime($content->release_date)) ?></span>
                                <span class="tag-separator"></span>
                                <span class="text-white fw-bold"><?= $content->getTextTypeAttribute() ?></span>
                                
                                <?php if ($content->type == 2 && !empty($seasons)) { ?>
                                    <span class="tag-separator"></span>
                                    <span class="text-white fw-bold"><?= count($seasons) ?> Seasons</span>
                                <?php } else if($content->type == 1) { 
                                    $duration = $content->Media->first()->duration;
                                    ?>
                                    <span class="tag-separator"></span>
                                    <span class="text-white fw-bold"><?= sprintf("%01d Hours %01d Minutes", floor($duration / 3600), floor(($duration % 3600) / 60)) ?></span>
                                <?php } ?>

                                <?php if ($content->adult_only) { ?>
                                    <span class="tag-separator"></span>
                                    <span class="badge text-bg-danger">Adult</span>
                                <?php } ?>
                            </div>
                            
                            <!-- Content description -->
                            <div class="col-12 col-md-11 col-lg-10 mb-4">
                                <p class="lead fw-medium text-container" id="expandableText">
                                    <?= htmlspecialchars($content->description) ?>
                                </p>
                            </div>

                            <script>
                                $(document).ready(function() {
                                    $("#expandableText").click(function() {
                                        $(this).toggleClass("text-container");
                                    });
                                });
                            </script>
                            
                            <!-- Action buttons -->
                            <div class="d-flex gap-2 mb-4">
                                <button class="btn btn-light d-inline-flex align-items-center justify-content-center" onclick="playMedia(<?= $content->type == 1 ? $content->Media->first()->id : $content->ChildMedia->first()->id ?>)">
                                    <i class="fas fa-play me-2"></i><?= $watchButtonText ?>
                                </button>
                                <?php if ($content->type == 2 && isset($content->total_episodes)) { ?>
                                    <button class="btn btn-secondary d-inline-flex align-items-center justify-content-center">
                                        <i class="fas fa-list me-2"></i><?= $content->ChildMedia->count() ?> Episodes
                                    </button>
                                <?php } ?>
                                <button class="btn btn-outline-light btn-lg d-flex align-items-center px-4 py-2 shadow-sm">
                                    <i class="fas fa-plus me-2"></i> Add to List
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
    </div>
    <div class="swiper-pagination"></div>
</div>

<?php 
include '../components/player.php';
?>

<script>
    const swiper = new Swiper('.swiper', {
        direction: 'horizontal',
        autoplay: {
            delay: 5000,
            disableOnInteraction: true,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
    });
</script>