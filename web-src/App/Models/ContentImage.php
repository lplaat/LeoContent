<?php

namespace Src\App\Models;

use Illuminate\Database\Eloquent\Model;
use Src\App\Controllers\TMDBController;

class ContentImage extends Model
{
    protected $table = 'content_image';

    public $timestamps = false;

    public function randomId() {

    }

    public function fetch() {
        $rootStorage = '/var/www/html/storage/';
        if (!file_exists($rootStorage)) {
            mkdir($rootStorage, 0777, true);
        }        

        $extension = pathinfo(parse_url($this->url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $this->path = $rootStorage . uniqid() . '.' . $extension;
        file_put_contents($this->path, file_get_contents($this->url));

        unset($this->url);
        $this->save();
    }

    public function url() {
        return str_replace('/var/www/html', '', $this->path);
    }
}