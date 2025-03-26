<?php

namespace Src\App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaDirectory extends Model
{
    protected $table = 'media_directory';
    protected $fillable = [
        'path',
        'type',
    ];

    public $timestamps = false;

    function getUnfinishedJobAttribute() {
        return Job::where('status', '!=', '2')->where('parent_id', $this->id)->first();
    }

    function getMediaAttribute() {
        return $this->hasMany(Media::class, 'media_directory_id', 'id');
    }

    function getTextTypeAttribute()
    {
        if($this->type == 1) {
            return 'Movie';
        } else if($this->type == 2) {
            return 'Show';
        }
    }
}