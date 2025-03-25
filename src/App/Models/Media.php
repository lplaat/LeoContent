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
        'content_id'
    ];

    function getContentAttribute()
    {
        return $this->hasOne(Content::class, 'id', 'content_id')->first();
    }
}