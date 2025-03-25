<?php

function scanDirectory($dir) {
    $files = scandir($dir);
    $paths = [];
    foreach ($files as $file) {
        if($file == '.' || $file == '..') continue;

        $newPath = $dir . "/" . $file;
        if (is_dir($newPath)) {
            array_merge($paths, scanDirectory($newPath));
        } else {
            $fileExtension = pathinfo($newPath, PATHINFO_EXTENSION);
            if(!in_array($fileExtension, ['mp4', 'avi', 'mkv'])) {
                continue;
            }

            $paths[] = $newPath;
        }
    }

    return $paths;
}