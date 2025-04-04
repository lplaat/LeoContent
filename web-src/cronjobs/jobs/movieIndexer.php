<?php

use Src\App\Controllers\TMDBController;
use Src\App\Controllers\VideoController;
use Src\App\Models\MediaDirectory;
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

    $nameParts = explode('.', $pathParts['filename']);
    $offset = count($nameParts) - 2;

    $title = implode(' ', array_slice($nameParts, 0, $offset));
    $year = array_slice($nameParts, $offset, 1)[0];

    $contents = $controller->searchMovies($title, $year);
    if($contents->count() == 0) {
        continue;
    }

    $content = $contents->first();
    $content->prepare();

    $media->content_id = $content->id;
    $media->save();
}