<?php

namespace Src\App\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Src\App\Models\Content;
use Src\App\Models\ContentImage;

class TMDBController extends Controller
{
    private $token;
    private const TMDB_API_BASE = 'https://api.themoviedb.org/3/';
    private const TMDB_IMAGE_BASE = 'https://image.tmdb.org/t/p/original/';
    
    // Content type constants
    private const TYPE_MOVIE = 1;
    private const TYPE_SHOW = 2;
    private const TYPE_EPISODE = 3;
    
    // Rate limiting constants
    private const MAX_RETRIES = 3;
    private const INITIAL_RETRY_DELAY = 1500000; // 1.5 seconds in microseconds
    private const RATE_LIMIT_STATUS = 429;

    public function __construct()
    {
        global $config;
        $this->token = $config['TMDB_TOKEN'];
    }

    private function curlRequest($url, $retryCount = 0) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Handle rate limiting (429 Too Many Requests)
        if ($httpCode === self::RATE_LIMIT_STATUS && $retryCount < self::MAX_RETRIES) {
            // Exponential backoff: wait longer with each retry
            $delay = self::INITIAL_RETRY_DELAY * pow(2, $retryCount);
            usleep($delay);
            
            // Recursively retry the request
            return $this->curlRequest($url, $retryCount + 1);
        }
        
        return json_decode($response);
    }

    private function buildApiUrl($endpoint, $params = []) {
        $url = self::TMDB_API_BASE . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }

    private function saveContentImage($content, $imagePath, $type) {
        $image = new ContentImage();
        $image->url = self::TMDB_IMAGE_BASE . $imagePath;
        $image->parent_id = $content->id;
        $image->type = $type;
        $image->fetch();
    }

    public function fetchImages($content) {
        $data = null;
        
        switch ($content->type) {
            case self::TYPE_MOVIE:
                $url = $this->buildApiUrl("movie/{$content->origin_id}/images");
                $data = $this->curlRequest($url);
                
                if ($content->Poster == null && !empty($data->posters)) {
                    $this->saveContentImage($content, $data->posters[0]->file_path, 'poster');
                }
                
                if ($content->Backdrop == null && !empty($data->backdrops)) {
                    $this->saveContentImage($content, $data->backdrops[0]->file_path, 'backdrop');
                }
                break;
                
            case self::TYPE_SHOW:
                $url = $this->buildApiUrl("tv/{$content->origin_id}/images");
                $data = $this->curlRequest($url);
                
                if ($content->Poster == null && !empty($data->posters)) {
                    $this->saveContentImage($content, $data->posters[0]->file_path, 'poster');
                }
                
                if ($content->Backdrop == null && !empty($data->backdrops)) {
                    $this->saveContentImage($content, $data->backdrops[0]->file_path, 'backdrop');
                }
                break;
                
            case self::TYPE_EPISODE:
                $url = $this->buildApiUrl("tv/{$content->Parent->origin_id}/season/{$content->season}/episode/{$content->episode}/images");
                $data = $this->curlRequest($url);
                
                if ($content->Poster == null && !empty($data->stills)) {
                    $this->saveContentImage($content, $data->stills[0]->file_path, 'poster');
                }
                break;
        }
    }

    private function searchLocalContent($title, $type, $year = null) {
        $query = Content::where('title', 'LIKE', '%' . $title . '%')->where('type', $type);
        
        if ($year) {
            $query->whereRaw('YEAR(release_date) = ?', [$year]);
        }
        
        return $query->get();
    }

    private function createContentFromData($data, $type) {
        $content = new Content();
        $content->origin_id = $data->id;
        $content->description = $data->overview ?? '';
        $content->adult_only = $data->adult ?? false;
        $content->type = $type;
        $content->is_prepared = false;
        
        if ($type == self::TYPE_MOVIE) {
            $content->title = $data->title;
            $content->release_date = date('Y-m-d H:i:s', strtotime($data->release_date ?? 'now'));
        } elseif ($type == self::TYPE_SHOW) {
            $content->title = $data->name;
            $content->release_date = date('Y-m-d H:i:s', strtotime($data->first_air_date ?? 'now'));
        } elseif ($type == self::TYPE_EPISODE) {
            $content->title = $data->name;
            $content->release_date = date('Y-m-d H:i:s', strtotime($data->air_date ?? 'now'));
            $content->episode = $data->episode_number;
            $content->season = $data->season_number;
            $content->parent_id = $data->parent_id ?? null;
        }
        
        $content->save();
        return $content;
    }

    public function searchMovies($title, $year = null) {
        // First do a local search inside the database
        $localResults = $this->searchLocalContent($title, self::TYPE_MOVIE, $year);
        if ($localResults->count() > 0) {
            return $localResults;
        }

        // Requesting metadata from TMDB
        $params = [
            'query' => $title,
            'page' => 1,
            'include_adult' => 'true'
        ];
        
        if ($year) {
            $params['year'] = $year;
        }
        
        $url = $this->buildApiUrl('search/movie', $params);
        $response = $this->curlRequest($url);
        
        $collection = new Collection();
        
        if (!$response || empty($response->results)) {
            return $collection;
        }

        // Creating contents
        foreach ($response->results as $movie) {
            $content = $this->createContentFromData($movie, self::TYPE_MOVIE);
            $collection->push($content);
        }

        return $collection;
    }

    public function searchShow($title, $year = null) {
        // First do a local search inside the database
        $localResults = $this->searchLocalContent($title, self::TYPE_SHOW, $year);
        if ($localResults->count() > 0) {
            return $localResults;
        }

        // Requesting metadata from TMDB
        $params = [
            'query' => $title,
            'include_adult' => 'true'
        ];
        
        if ($year) {
            $params['year'] = $year;
        }
        
        $url = $this->buildApiUrl('search/tv', $params);
        $response = $this->curlRequest($url);
        
        $collection = new Collection();
        
        if (!$response || empty($response->results)) {
            return $collection;
        }

        // Creating contents
        foreach ($response->results as $show) {
            // Check if this show already exists in database
            $showsCached = Content::where('origin_id', $show->id)->where('type', self::TYPE_SHOW);
            if ($showsCached->count() !== 0) {
                $collection->push($showsCached->first());
                continue;
            }    

            $content = $this->createContentFromData($show, self::TYPE_SHOW);
            $collection->push($content);
        }

        return $collection;
    }   

    public function getShowData($id) {
        $url = $this->buildApiUrl("tv/$id", ['language' => 'en-US']);
        $show = $this->curlRequest($url);
        
        if (isset($show->success) && !$show->success) {
            return null;
        }

        return $show;
    }

    public function getEpisode($showContent, $episodeNumber, $seasonNumber) {
        $url = $this->buildApiUrl(
            "tv/{$showContent->origin_id}/season/$seasonNumber/episode/$episodeNumber", 
            ['language' => 'en-US']
        );
        
        $episode = $this->curlRequest($url);
        if (isset($episode->success) && !$episode->success) {
            return null;
        }

        // Check if episode already exists in database
        $episodesCached = Content::where('origin_id', $episode->id)->where('type', self::TYPE_EPISODE);
        if ($episodesCached->count() !== 0) {
            return $episodesCached->first();
        }

        // Add parent_id for episode creation
        $episode->parent_id = $showContent->id;
        
        return $this->createContentFromData($episode, self::TYPE_EPISODE);
    }

    public function getEpisodesFromSeason($showContent, $seasonNumber) {
        $url = $this->buildApiUrl(
            "tv/{$showContent->origin_id}/season/$seasonNumber", 
            ['language' => 'en-US']
        );
        
        $request = $this->curlRequest($url);
        if (isset($request->success) && !$request->success) {
            return null;
        }

        $episodes = [];
        foreach ($request->episodes as $episode) {
            // Check if episode already exists in database
            $episodesCached = Content::where('origin_id', $episode->id)->where('type', self::TYPE_EPISODE);
            if ($episodesCached->count() !== 0) {
                $episodes[] = $episodesCached->first();
                continue;
            }

            // Add parent_id for episode creation
            $episode->parent_id = $showContent->id;
            
            $content = $this->createContentFromData($episode, self::TYPE_EPISODE);
            $episodes[] = $content;
        } 

        return $episodes;
    }
}