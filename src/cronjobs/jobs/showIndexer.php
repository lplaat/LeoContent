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

    $patterns = [
        // Example: Show.Name.S01E02
        '/^(?P<show_name>[A-Za-z0-9&.]+)\.S(?P<season>\d{2})E(?P<episode>\d{2})/',
    
        // Example: Show Name - 1x02 (allowing spaces, dots, or dashes in the name)
        '/^(?P<show_name>[A-Za-z0-9&.\s-]+)[. -]+(?P<season>\d{1,2})x(?P<episode>\d{2})/',
    
        // Example: Show Name - 102 (season 1, episode 02; season can be 1 or 2 digits)
        '/^(?P<show_name>[A-Za-z0-9&.\s-]+)[. -]+(?P<season>\d{1,2})(?P<episode>\d{2})/',

        // New pattern: Show Title (Year) - S04E06 - Additional Info...
        '/^(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*\(\d{4}\)\s*-\s*S(?P<season>\d{2})E(?P<episode>\d{2})/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $pathParts['filename'], $matches)) {
            // Normalize the show name by replacing dots or dashes with spaces and trimming extra whitespace
            $matches['show_name'] = trim(preg_replace('/[.\-]+/', ' ', $matches['show_name']));
            $matches['season'] = (int)$matches['season'];
            $matches['episode'] = (int)$matches['episode'];
            
            break;
        }
    }

    $contents = $controller->searchShow($matches['show_name']);
    if($contents->count() == 0) {
        continue;
    }

    $content = $contents->first();
    $content->prepare();
}