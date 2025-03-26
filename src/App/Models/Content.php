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
        'episode',
        'season',
        'type'
    ];

    function getTextTypeAttribute()
    {
        if($this->type == 1) {
            return 'Movie';
        } else if($this->type == 2) {
            return 'Show';
        } else if($this->type == 3) {
            return 'Episode';
        }
    }

    function getMediaAttribute()
    {
        return $this->hasMany(Media::class, 'content_id', 'id');
    }

    function getChildrenAttribute()
    {
        return $this->hasMany(Content::class, 'parent_id', 'id');
    }

    function getChildMediaAttribute() {
        return $this->hasManyThrough(Media::class, Content::class, 'parent_id', 'content_id', 'id', 'id');
    }

    function getParentAttribute() {
        return $this->hasOne(Content::class, 'id', 'parent_id')->first();
    }

    public function prepare() {
        $controller = new TMDBController();
        $controller->fetchImages($this);

        if($this->type == 2) {
            $show = $controller->getShowData($this->origin_id);

            if($show !== null) {
                $this->total_episodes = $show->number_of_episodes;
            }
        }

        $this->is_prepared = true;
        $this->save();
    }

    public function getPosterAttribute() {
        return ContentImage::where('parent_id', $this->id)->where('type', 'poster')->first();
    }

    public function getBackdropAttribute() {
        return ContentImage::where('parent_id', $this->id)->where('type', 'backdrop')->first();
    }
}