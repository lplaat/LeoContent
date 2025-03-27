<?php

use Src\App\Models\Content;

include '../config.php';

$content = Content::find($_GET['id']);
if ($content == null) {
    header('location: /home');
    die();
}

$transparentNav = true;
include '../components/header.php';

// Set different content for movies vs shows
$contentType = $content->type == 1 ? 'movie' : 'show';
$watchButtonText = $contentType == 'movie' ? 'Play' : 'Play Episode 1';

// Get seasons for shows
$seasons = [];
if ($content->type == 2) {
    $allEpisodes = $content->Children->get();
    foreach ($allEpisodes as $episode) {
        if (!isset($seasons[$episode->season])) {
            $seasons[$episode->season] = [];
        }
        $seasons[$episode->season][] = $episode;
    }
    // Sort seasons by number
    ksort($seasons);
}

// Get current season (default to first season)
$currentSeason = isset($_GET['season']) ? intval($_GET['season']) : (count($seasons) > 0 ? array_key_first($seasons) : 1);
?>

<style>
/* Minimal custom styles to complement Bootstrap */
body {
    background-color: #141414;
    color: #fff;
}

.hero-section {
    position: relative;
    min-height: 100vh;
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
}

.hero-content {
    position: relative;
    z-index: 1;
}

.episode-card {
    position: relative;
    transition: transform 0.2s;
    height: 100%;
    overflow: hidden;
}

.episode-card:hover {
    transform: scale(1.03);
    z-index: 2;
}

.episode-number {
    position: absolute;
    top: 10px;
    left: 10px;
}

.episode-info {
    position: absolute;
    bottom: 0;
    width: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.4), transparent);
    padding: 1rem;
}

.tag-separator::after {
    content: "•";
    margin: 0 0.5rem;
}

.episode-info {
    position: absolute;
    top: 125px;
}

.episode-card:hover .episode-info {
    bottom: 0;
    top: unset;
}
</style>

<div class="bg-dark text-white">
    <!-- Hero Section -->
    <section class="hero-section d-flex justify-content-center align-items-center">
        <img src="<?= $content->backdrop ? $content->backdrop->Url() : '' ?>" class="hero-image" alt="<?= htmlspecialchars($content->title) ?> backdrop">
        
        <div class="container hero-content">
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
                        <p class="lead fw-medium"><?= htmlspecialchars($content->description) ?></p>
                    </div>

                    <!-- Action buttons -->
                    <div class="d-flex gap-2 mb-4">
                        <button class="btn btn-light d-inline-flex align-items-center justify-content-center" onclick="watchContent()">
                            <i class="fas fa-play me-2"></i><?= $watchButtonText ?>
                        </button>
                        <?php if ($content->type == 2 && isset($content->total_episodes)) { ?>
                            <button class="btn btn-secondary d-inline-flex align-items-center justify-content-center">
                                <i class="fas fa-list me-2"></i><?= $content->ChildMedia->count() ?> Episodes
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Episodes section for shows -->
    <?php if ($content->type == 2 && !empty($seasons)) { ?>
        <section class="py-5" id="episodeSelector">
            <div class="container">
                <!-- Season selector -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h3 fw-bold mb-0"><?= htmlspecialchars($content->title) ?></h2>
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="seasonDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Season <?= $currentSeason ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="seasonDropdown">
                            <?php foreach (array_keys($seasons) as $seasonNum) { ?>
                                <li>
                                    <a class="dropdown-item <?= $seasonNum == $currentSeason ? 'active' : '' ?>" 
                                       href="?id=<?= $content->id ?>&season=<?= $seasonNum ?>#episodeSelector">
                                       Season <?= $seasonNum ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Episodes grid using Bootstrap cards and grid -->
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
                    <?php 
                    if (isset($seasons[$currentSeason])) {
                        $currentSeasonEpisodes = $seasons[$currentSeason];
                        usort($currentSeasonEpisodes, function($a, $b) {
                            return $a->episode - $b->episode;
                        });
                        
                        foreach ($currentSeasonEpisodes as $episode) { ?>
                            <div class="col">
                                <div class="bg-dark text-white h-100 episode-card rounded" onclick="watchEpisode(<?= $episode->id ?>)">
                                    <img 
                                        src="<?= $episode->poster ? $episode->poster->Url() : ($content->backdrop ? $content->backdrop->Url() : '') ?>" 
                                        class="card-img rounded" 
                                        alt="Episode <?= $episode->episode ?>"
                                    >
                                    <div class="episode-number badge bg-dark bg-opacity-75">Episode <?= $episode->episode ?></div>
                                    <div class="episode-info rounded">
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($episode->title) ?></h5>
                                        <p class="card-text small"><?= htmlspecialchars(substr($episode->description, 0, 120)) ?>...</p>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    } 
                    ?>
                </div>
            </div>
        </section>
    <?php } ?>
    
    <!-- Info footer with minimal details -->
    <div class="py-4 border-top border-secondary">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto me-3">
                    <?php if ($content->poster) { ?>
                        <img src="<?= $content->poster->Url() ?>" 
                             alt="<?= htmlspecialchars($content->title) ?> Poster" 
                             class="img-fluid rounded shadow" style="max-height: 80px;">
                    <?php } ?>
                </div>
                <div class="col">
                    <div class="d-flex flex-wrap gap-4">
                        <div>
                            <small class="text-muted d-block">Released</small>
                            <span><?= date('F Y', strtotime($content->release_date)) ?></span>
                        </div>
                        <?php if ($content->type == 2) { ?>
                            <div>
                                <small class="text-muted d-block">Episodes</small>
                                <span><?= $content->ChildMedia->count() ?></span>
                            </div>
                        <?php } ?>
                        <?php if ($content->adult_only) { ?>
                            <div>
                                <span class="badge text-bg-danger">18+</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function watchContent() {
    <?php if ($content->type == 1) { ?>
        // This is a movie
        console.log("Watching movie: <?= htmlspecialchars($content->title) ?>");
        // Add your logic here to watch the movie
    <?php } else { ?>
        // This is a show, find first episode of current season
        <?php 
        if (!empty($seasons) && isset($seasons[$currentSeason]) && count($seasons[$currentSeason]) > 0) {
            // Sort episodes by episode number
            $seasonEpisodes = $seasons[$currentSeason];
            usort($seasonEpisodes, function($a, $b) {
                return $a->episode - $b->episode;
            });
            $firstEpisode = $seasonEpisodes[0];
        ?>
            watchEpisode(<?= $firstEpisode->id ?>);
        <?php } else { ?>
            alert("No episodes available");
        <?php } ?>
    <?php } ?>
}

function watchEpisode(episodeId) {
    console.log("Watching episode: " + episodeId);
    // Add your logic here to watch the episode
}
</script>

<?php
include '../components/footer.php';
?>