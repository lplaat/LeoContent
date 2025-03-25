<?php

use Src\App\Controllers\TMDBController;
use Src\App\Models\MediaDirectory;
use Src\App\Models\Media;

$mediaDirectory = MediaDirectory::find($job->parent_id);

$paths = scanDirectory($mediaDirectory->path);

$existingMedia = $mediaDirectory->Media;
$existingPathsArray = $existingMedia->pluck('path')->toArray();

// Search and create new Media
foreach ($paths as $path) {
    if (!in_array($path, $existingPathsArray)) {
        $media = New Media();
        $media->path = $path;
        $media->media_directory_id = $mediaDirectory->id;
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

    $content = $controller->search($title, $year);
    if($content->count() == 0) {
        continue;
    }

    $content = $content->first();
    $content->prepare();

    $media->content_id = $content->id;
    $media->save();
}