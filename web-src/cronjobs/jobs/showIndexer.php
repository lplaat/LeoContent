<?php

use Src\App\Controllers\TMDBController;
use Src\App\Models\MediaDirectory;
use Src\App\Controllers\VideoController;
use Src\App\Models\Media;

$mediaDirectory = MediaDirectory::find($job->parent_id);

$paths = VideoController::scanDirectory($mediaDirectory->path);

$existingMedia = $mediaDirectory->Media;
$existingPathsArray = $existingMedia->pluck('path')->toArray();

// Search and create new Media
foreach ($paths as $path) {
    if (!in_array($path, $existingPathsArray)) {
        $media = New Media();
        $media->path = $path;
        $media->media_directory_id = $mediaDirectory->id;
        $media->duration = VideoController::getDuration($path);
        $media->quality = VideoController::getVideoQuality($path);
        $media->save();
    }
}

// Delete media that no longer exist
foreach ($existingMedia as $media) {
    if (!in_array($media->path, $paths)) {
        $media->delete();
    }
}

// Automatic search and find the content
$controller = new TMDBController();
foreach ($mediaDirectory->Media->whereNull('content_id')->get() as $media) {
    $pathParts = pathinfo($media->path);
    $nameData = VideoController::ExtraNameData($pathParts['filename'], 2);
    if($nameData == null) {
        continue;
    }

    $contents = $controller->searchShow($nameData['show_name']);
    if($contents->count() == 0) {
        continue;
    }

    $show = $contents->first();
    $show->prepare();

    $episode = $controller->getEpisode($show, $nameData['episode'], $nameData['season']);
    if($episode !== null) {
        $episode->prepare();
        
        $media->content_id = $episode->id;
        $media->save();
    }
}