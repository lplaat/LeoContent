<?php

use Src\App\Models\Stream;
use Src\App\Models\Media;

include 'handler.php';

function getStream($request) {
    global $config;
    $media = Media::find($request['id'] ?? 0);
    if($media == null) {
        return [
            'success' => false,
            'message' => 'Media not found',
        ];
    }

    $stream = new Stream();
    $stream->code = Stream::generateCode();
    $stream->user_id = $_SESSION['userId'];
    $stream->media_id = $media->id;
    $stream->save();

    return [
        'success' => true,
        'data' => [
            'url' => $config['MEDIA_SERVER_URL'] . '/playlist/' . $stream->code . '/master.m3u8'
        ]
    ];
}