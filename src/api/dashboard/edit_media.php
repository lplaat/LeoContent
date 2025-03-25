<?php

use Src\App\Models\Media;

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
    $media->save();

    return [
        'success' => true,
        'message' => 'Media is saved',
    ];
}