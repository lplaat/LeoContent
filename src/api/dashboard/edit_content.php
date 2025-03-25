<?php

use Src\App\Models\Content;

include 'handler.php';

function saveContent($request) {
    $content = Content::find($request['id'] ?? 0);
    if($content == null) {
        return [
            'success' => false,
            'message' => 'Content not found',
        ];
    }

    $fields = ['title', 'description', 'release_date'];
    foreach($fields as $field) {
        $content->{$field} = $request[$field];
    }
    $content->save();

    $checkFields = ['adult_only'];
    foreach($checkFields as $checkField) {
        $content->{$checkField} = ($request[$checkField] ?? '') == 'on';
    }
    $content->save();

    return [
        'success' => true,
        'message' => 'Content is saved',
    ];
}