<?php

use Src\App\Models\Content;
use Src\App\Models\MediaDirectory;
use Src\App\Models\Media;
use Src\App\Models\Job;

include 'handler.php';

function getDirectories($request)
{
    $draw = $request['draw'] ?? 1;
    $start = $request['start'] ?? 0;
    $length = $request['length'] ?? 10;
    if($length < 0) $length = 100;

    $query = MediaDirectory::query();

    $totalRecords = MediaDirectory::count();
    $filteredRecords = $query->count();

    $directories = $query->offset($start)
        ->limit($length)
        ->get();

    $data = [];
    foreach ($directories as $directory) {
        $data[] = [
            'id' => $directory->id,
            'path' => $directory->path,
            'type' => $directory->TextType,
            'media-amount' => $directory->Media->count(),
            'action' => '<a href="edit_directory.php?id=' . $directory->id . '"><i class="fa-solid fa-eye"></i></a>'
        ];
    }

    return [
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ];
}

function getMedia($request)
{
    $draw = $request['draw'] ?? 1;
    $start = $request['start'] ?? 0;
    $length = $request['length'] ?? 10;
    if($length < 0) $length = 100;

    $query = Media::where('media_directory_id', $request['media_directory_id'] ?? 0);

    $totalRecords = MediaDirectory::count();
    $filteredRecords = $query->count();

    $medias = $query->offset($start)
        ->limit($length)
        ->get();

    $data = [];
    foreach ($medias as $media) {


        $data[] = [
            'id' => $media->id,
            'filename' => basename($media->path),
            'status' => ($media->content_id == null) ? '<b class="bg-danger rounded p-2 text-white">No metadata</b>' : '<b class="bg-success rounded p-2 text-white">Found metadata</b>',
            'action' => '<a href="edit_media.php?id=' . $media->id . '"><i class="fa-solid fa-eye"></i></a>'
        ];
    }

    return [
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ];
}

function getContent($request)
{
    $draw = $request['draw'] ?? 1;
    $start = $request['start'] ?? 0;
    $length = $request['length'] ?? 10;
    if($length < 0) $length = 100;

    $query = Content::query()->where('is_prepared', 1);

    $totalRecords = MediaDirectory::count();
    $filteredRecords = $query->count();

    $contentData = $query->offset($start)
        ->limit($length)
        ->get();

    $data = [];
    foreach ($contentData as $content) {
        if($content->type == 1) {
            $status = ($content->Media->count() == 0) ? '<b class="bg-danger rounded p-2 text-white">Missing Media</b>' : '<b class="bg-success rounded p-2 text-white">Found Media</b>';
        } else if($content->type == 2) {
            $status = ($content->ChildMedia->count() != $content->total_episodes) ? '<b class="bg-danger rounded p-2 text-white">Missing Media ' . $content->ChildMedia->count() . '/' . $content->total_episodes. '</b>' : '<b class="bg-success rounded p-2 text-white">Found Media</b>';
        }else if($content->type == 3) {
            $status = ($content->Media->count() == 0) ? '<b class="bg-danger rounded p-2 text-white">Missing Media</b>' : '<b class="bg-success rounded p-2 text-white">Found Media</b>';
        }

        $data[] = [
            'id' => $content->id,
            'title' => $content->title,
            'release-date' => date('Y-m-d', strtotime($content->release_date)),
            'type' => $content->TextType,
            'status' => $status,
            'action' => '<a href="edit_content.php?id=' . $content->id . '"><i class="fa-solid fa-eye"></i></a>'
        ];
    }

    return [
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ];
}

function getJobs($request) {
    $draw = $request['draw'] ?? 1;
    $start = $request['start'] ?? 0;
    $length = $request['length'] ?? 10;
    if($length < 0) $length = 100;

    $query = Job::query()->orderBy('created_at', 'desc');

    $totalRecords = Job::count();
    $filteredRecords = $query->count();

    $jobData = $query->offset($start)
        ->limit($length)
        ->get();

    $data = [];
    foreach ($jobData as $job) {
        $data[] = [
            'id' => $job->id,
            'type' => $job->TypeName,
            'status' => '<b class="bg-' . $job->ColorStatus . ' rounded p-2 text-white">' . $job->TextStatus . '</b>',
            'created-at' => date('Y-m-d H:i', strtotime($job->updated_at))
        ];
    }

    return [
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ];
}