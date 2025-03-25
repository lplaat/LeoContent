<?php

use Src\App\Models\MediaDirectory;
use Src\App\Models\Job;

include 'handler.php';

function reScanDirectory($request) {
    $mediaDirectory = MediaDirectory::find($request['id'] ?? 0);
    if($mediaDirectory == null) {
        return [
            'success' => false,
            'message' => 'Media directory not found',
        ];
    }

    // Created a scan job
    $job = new Job();
    $job->type = $mediaDirectory->type;
    $job->status = 0;
    $job->parent_id = $mediaDirectory->id;
    $job->save();

    return [
        'success' => true,
        'message' => 'Created rescan job',
        'redirect' => 'edit_directory.php?id=' . $mediaDirectory->id,
    ];
}

function createMediaDirectory($request)
{
    if(!is_dir($request['path'])) {
        return [
            'success' => false,
            'message' => 'Directory does not exist',
        ];
    }

    // Created content directory
    $mediaDirectory = new MediaDirectory();
    $mediaDirectory->type = $request['type'] == 'movies' ? 1 : 2;
    $mediaDirectory->path = $request['path'];
    $mediaDirectory->save();

    // Created a scan job
    $job = new Job();
    $job->type = $mediaDirectory->type;
    $job->status = 0;
    $job->parent_id = $mediaDirectory->id;
    $job->save();

    return [
        'success' => true,
        'message' => 'Content directory created',
        'redirect' => 'edit_directory.php?id=' . $mediaDirectory->id,
    ];
}