<?php

use Src\App\Models\Job;

include __DIR__ . '/../config.php';

$job = Job::where('status', 0)->orderBy('created_at', 'desc')->first();
if($job) {
    $job->status = 1;
    $job->save();

    if($job->type == 1) {
        include './jobs/movieIndexer.php';
    } else if($job->type == 2) {
        include './jobs/showIndexer.php';
    }

    $job->status = 2;
    $job->save();
}