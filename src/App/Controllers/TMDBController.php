<?php

namespace Src\App\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Src\App\Models\Content;
use Src\App\Models\ContentImage;

class TMDBController extends Controller
{
    private $token;

    public function __construct()
    {
        global $config;

        $this->token = $config['TMDB_TOKEN'];
    }

    private function curlRequest($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);

        return json_decode($response);
    }


    public function fetchImages($content) {
        $url = 'https://api.themoviedb.org/3/movie/' . $content->origin_id . '/images';
        $data = $this->curlRequest($url);

        if($content->Poster == null) {
            $poster = new ContentImage();
            $poster->url = 'https://image.tmdb.org/t/p/original/' . $data->posters[0]->file_path;
            $poster->parent_id = $content->id;
            $poster->type = 'poster';
            $poster->fetch();
        }

        if($content->Backdrop == null) {
            $poster = new ContentImage();
            $poster->url = 'https://image.tmdb.org/t/p/original/' . $data->backdrops[0]->file_path;
            $poster->parent_id = $content->id;
            $poster->type = 'backdrop';
            $poster->fetch();
        }
    }

    public function search($title, $year = null) {
        // First do a local search inside the database
        $content = Content::where('title', 'LIKE', '%' . $title . '%');
        if($year) {
            $content->whereRaw('YEAR(release_date) = ?', [$year]);
        }

        $content = $content->get();
        if($content->count() > 0) {
            return $content;
        }

        // Requesting metadata
        $url = 'https://api.themoviedb.org/3/search/movie?query=' . urlencode($title) . '&page=1&include_adult=true';
        if($year) {
            $url .= '&year=' . $year;
        }

        $collection = new Collection();

        $response = $this->curlRequest($url)->results;
        foreach($response as $movie) {
            $content = New Content();
            $content->origin_id = $movie->id;
            $content->title = $movie->title;
            $content->description = $movie->overview;
            $content->release_date = date('Y-m-d h:i:s', strtotime($movie->release_date));
            $content->adult_only = $movie->adult;
            $content->type = 1;
            $content->save();

            $collection->push($content);
        }

        return $collection;
    }
}