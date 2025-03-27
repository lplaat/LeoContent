<?php

namespace Src\App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    public $timestamps = false;

    protected $fillable = [
        'path',
        'media_directory_id',
        'duration',
        'quality',
        'content_id'
    ];


    function getMediaDirectoryAttribute() {
        return $this->hasOne(MediaDirectory::class, 'id', 'media_directory_id')->first();
    }

    function getContentAttribute()
    {
        return $this->hasOne(Content::class, 'id', 'content_id')->first();
    }
}