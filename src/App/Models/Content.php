<?php

namespace Src\App\Models;

use Illuminate\Database\Eloquent\Model;
use Src\App\Controllers\TMDBController;

class Content extends Model
{
    protected $table = 'content';

    protected $fillable = [
        'origin_id',
        'title',
        'description',
        'release_date',
        'adult_only',
        'parent_id',
        'type'
    ];

    function getTextTypeAttribute()
    {
        if($this->type == 1) {
            return 'Movie';
        } else if($this->status == 2) {
            return 'Show';
        } else if($this->status == 2) {
            return 'Episode';
        }
    }

    function getMediaAttribute()
    {
        return $this->hasMany(Media::class, 'content_id', 'id');
    }

    public function prepare() {
        $controller = new TMDBController();

        if($this->type == 1) {
            $controller->fetchImages($this,);
        }
    }

    public function getPosterAttribute() {
        return ContentImage::where('parent_id', $this->id)->where('type', 'poster')->first();
    }

    public function getBackdropAttribute() {
        return ContentImage::where('parent_id', $this->id)->where('type', 'backdrop')->first();
    }
}