<?php

use Src\App\Models\Media;
use Src\App\Models\Content;
use Src\App\Controllers\TMDBController;

include 'handler.php';

function saveMedia($request) {
    $media = Media::find($request['id'] ?? 0);
    if($media == null) {
        return [
            'success' => false,
            'message' => 'Media not found',
        ];
    }

    $fields = ['path'];
    foreach($fields as $field) {
        $media->{$field} = $request[$field];
    }

    if(!empty($request['content_id'])) {
        $content = Content::find($request['content_id']);
        if($content == null) {
            return [
                'success' => false,
                'message' => 'Content not found',
            ];
        }

        if(!$content->is_prepared) {
            $content->prepare();
        }

        if(!$content->Parent->is_prepared) {
            $content->Parent->prepare();
        }

        $media->content_id = $content->id;
    }

    $media->save();

    return [
        'success' => true,
        'reload' => true,
        'message' => 'Media is saved',
    ];
}

function manualEpisodeSearch($request) {
    $episodes = [];

    $controller = new TMDBController();
    $shows = $controller->searchShow($request['show_name']);
    foreach ($shows as $show) {
        if(!empty($request['episode_number'] ?? '') && !empty($request['season_number'] ?? '')) {
            $episode = $controller->getEpisode($show, intval($request['episode_number']), intval($request['season_number']));
            if($episode !== null) {
                $episodes[] = $episode; 
            }
        }else if(!empty($request['season_number'] ?? '')) {
            $episodes = array_merge($episodes, $controller->getEpisodesFromSeason($show, intval($request['season_number'])) ?? []);
        }
    }

    $display = [];
    $amount = 0;
    $skip = $request['start'];
    foreach($episodes as $episode) {
        if($skip > 0) {
            $skip -= 1;
            continue;
        }

        if($amount >= $request['length']) {
            break;
        }

        $display[] = [
            'show' => $episode->Parent->title,
            'name' => $episode->title,
            'episode' => $episode->episode,
            'season' => $episode->season,
            'action' => '<a href="#" onclick="newContent(' . $episode->id . ')"><i class="fa-solid fa-plus"></i></a>',
        ];

        $amount++;
    }

    $data =  [
        'draw' => $request['draw'] ?? 1,
        'recordsTotal' => count($episodes),
        'recordsFiltered' => count($episodes),
        'data' => $display
    ];

    return $data;
}