<?php

namespace Src\App\Controllers;

class VideoController
{
    private static function runFfmpegCommand($path, $options)
    {
        $command = "ffprobe -i " . escapeshellarg($path) . " " . $options . " 2>&1";
        return shell_exec($command);
    }

    public static function getDuration($path)
    {
        $output = self::runFfmpegCommand($path, "-v error -select_streams v:0 -show_entries format=duration -of csv=p=0");
        return trim($output);
    }

    public static function getVideoQuality($path)
    {
        $output = self::runFfmpegCommand($path, "-v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0");
        list($width, $height) = array_map('intval', explode(',', trim($output)));
        return $width ."x" . $height;
    }

    public static function scanDirectory($dir) {
        $files = scandir($dir);
        $paths = [];
        foreach ($files as $file) {
            if($file == '.' || $file == '..') continue;
    
            $newPath = $dir . "/" . $file;
            if (is_dir($newPath)) {
                $paths = array_merge($paths, self::scanDirectory($newPath));
            } else {
                $fileExtension = pathinfo($newPath, PATHINFO_EXTENSION);
                if(!in_array($fileExtension, ['mp4', 'avi', 'mkv'])) {
                    continue;
                }
    
                $paths[] = $newPath;
            }
        }
    
        return $paths;
    }

    public static function ExtraNameData($filename, $type) {
        if($type == 2) {
            $patterns = [
                // Existing patterns...
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*S(?P<season>\d{2})E(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*S(?P<season>\d{2})E(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)[. -]+(?P<season>\d{1,2})x(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)[. -]+(?P<season>\d{1,2})(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*\(\d{4}\)\s*-\s*S(?P<season>\d{2})E(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)\.(\d{4})\.S(?P<season>\d{2})E(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&._-]+)_(\d{4})_(?P<season>\d{1,2})x(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+)\s+Season\s+(?P<season>\d{1,2})\s+Episode\s+(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+)\s+-\s+Episode\s+(?P<episode>\d{2})\s+of\s+Season\s+(?P<season>\d{1,2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s+)?(?P<show_name>[A-Za-z0-9&.\s-]+)\s+S(?P<season>\d{2})E(?P<episode>\d{2})(?:\s+(?P<title>[A-Za-z0-9&.\s-]+))?(?:\s+\[[^\]]+\])*$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*(?P<season>\d{1,2})(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*\[S(?P<season>\d{2})E(?P<episode>\d{2})\](?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/', 
                '/^(?:\[[^\]]+\]\s*)*(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*-\s*(?P<season>\d{1,2})(?P<episode>\d{2})(?:\s+[^\[\]]+)?(?:\s*\[[^\]]+\])*\.\w+$/',
                '/^(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*\(\d{4}\)\s*-\s*S(?P<season>\d{2})E(?P<episode>\d{2})(?:\s*-\s*[^()]+)?(?:\s*\([^\)]+\))*$/',
                '/^(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*S(?P<season>\d{2})e(?P<episode>\d{2})(?:\s*-?\s*(?P<title>.+))?$/',
                '/^(?P<show_name>[A-Za-z0-9&.\s-]+?)\.S(?P<season>\d{2})E(?P<episode>\d{2})(?:\.[^.]+)*$/',
                '/^(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*S(?P<season>\d{2})E(?P<episode>\d{2})(?:\s+(?P<title>.+))?\.(?:\w+)?$/',
                '/^(?P<show_name>[A-Za-z0-9&.\s-]+?)\s*S(?P<season>\d{2})E(?P<episode>\d{2})(?:\s+(?P<title>.+))?$/'
            ];
    
            $matches = null;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $filename, $matches)) {
                    // Normalize the show name by replacing dots or dashes with spaces and trimming extra whitespace
                    $matches['show_name'] = trim(preg_replace('/[.\-]+/', ' ', $matches['show_name']));
                    $matches['season'] = (int)$matches['season'];
                    $matches['episode'] = (int)$matches['episode'];
                    
                    break;
                }
            }

            return $matches;
        }
    }
}
