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
        if($content->type == 1) {
            $url = 'https://api.themoviedb.org/3/movie/' . $content->origin_id . '/images';
            $data = $this->curlRequest($url);
        } else if($content->type == 2) {
            $url = 'https://api.themoviedb.org/3/tv/' . $content->origin_id . '/images';
            $data = $this->curlRequest($url);
        } else if($content->type == 3) {
            $url = 'https://api.themoviedb.org/3/tv/' . $content->Parent->origin_id . '/season/' . $content->season . '/episode/' . $content->episode . '/images';
            $data = $this->curlRequest($url);

            if($content->Poster == null) {
                $poster = new ContentImage();
                $poster->url = 'https://image.tmdb.org/t/p/original/' . $data->stills[0]->file_path;
                $poster->parent_id = $content->id;
                $poster->type = 'poster';
                $poster->fetch();
            }
            return;
        }

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

    public function searchMovies($title, $year = null) {
        // First do a local search inside the database
        $content = Content::where('title', 'LIKE', '%' . $title . '%')->where('type', 1);
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

        // Creating contents
        $response = $this->curlRequest($url)->results;
        foreach($response as $movie) {
            $content = New Content();
            $content->origin_id = $movie->id;
            $content->title = $movie->title;
            $content->description = $movie->overview;
            $content->release_date = date('Y-m-d h:i:s', strtotime($movie->release_date));
            $content->adult_only = $movie->adult;
            $content->type = 1;
            $content->is_prepared = false;
            $content->save();

            $collection->push($content);
        }

        return $collection;
    }

    public function searchShow($title, $year = null) {
        // First do a local search inside the database
        $content = Content::where('title', 'LIKE', '%' . $title . '%')->where('type', 2);
        if($year) {
            $content->whereRaw('YEAR(release_date) = ?', [$year]);
        }

        if($content->count() > 0) {
            $content = $content->get();
            return $content;
        }

        // Requesting metadata
        $url = 'https://api.themoviedb.org/3/search/tv?query=' . urlencode($title);
        if($year) {
            $url .= '&year=' . $year;
        }

        $collection = new Collection();

        // Creating contents
        $response = $this->curlRequest(url: $url);
        foreach($response->results as $show) {
            $showsCached = Content::where('origin_id', $show->id)->where('type', 2);
            if($showsCached->count() !== 0) {
                $collection->push($showsCached->first());
                continue;
            }    

            $content = New Content();
            $content->origin_id = $show->id;
            $content->title = $show->name;
            $content->description = $show->overview;
            $content->release_date = date('Y-m-d h:i:s', strtotime($show->first_air_date));
            $content->adult_only = $show->adult;
            $content->type = 2;
            $content->is_prepared = false;
            $content->save();

            $collection->push($content);
        }

        return $collection;
    }   

    function getShowData($id){
        $url = 'https://api.themoviedb.org/3/tv/' . $id . '?language=en-US';
        
        $show = $this->curlRequest($url);
        if(isset($show->success) && !$show->success) {
            return null;
        }

        return $show;
    }

    function getEpisode($showContent, $episodeNumber, $seasonNumber) {
        $url = 'https://api.themoviedb.org/3/tv/' . $showContent->origin_id . '/season/' . $seasonNumber . '/episode/' . $episodeNumber . '?language=en-US';
        
        $episode = $this->curlRequest($url);
        if(isset($episode->success) && !$episode->success) {
            return null;
        }

        $episodesCached = Content::where('origin_id', $episode->id)->where('type', 3);
        if($episodesCached->count() !== 0) {
            return $episodesCached->first();
        }

        $content = New Content();
        $content->origin_id = $episode->id;
        $content->title = $episode->name;
        $content->description = $episode->overview;
        $content->release_date = date('Y-m-d h:i:s', strtotime($episode->air_date));
        $content->parent_id = $showContent->id;
        $content->episode = $episodeNumber;
        $content->season = $seasonNumber;
        $content->type = 3;
        $content->is_prepared = false;
        $content->save();

        return $content;
    }

    function getEpisodesFromSeason($showContent, $seasonNumber) {
        $url = 'https://api.themoviedb.org/3/tv/' . $showContent->origin_id . '/season/' . $seasonNumber . '?language=en-US';
        $request = $this->curlRequest($url);
        if(isset($request->success) && !$request->success) {
            return null;
        }

        $episodes = [];
        foreach($request->episodes as $episode) {
            $episodesCached = Content::where('origin_id', $episode->id)->where('type', 3);;
            if($episodesCached->count() !== 0) {
                $episodes[] = $episodesCached->first();
                continue;
            }

            $content = New Content();
            $content->origin_id = $episode->id;
            $content->title = $episode->name;
            $content->description = $episode->overview;
            $content->release_date = date('Y-m-d h:i:s', strtotime($episode->air_date));
            $content->parent_id = $showContent->id;
            $content->episode = $episode->episode_number;
            $content->season = $episode->season_number;
            $content->type = 3;
            $content->is_prepared = false;
            $content->save();

            $episodes[] = $content;
        } 

        return $episodes;
    }
}