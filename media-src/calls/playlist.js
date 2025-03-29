import { errorResponse, response } from "./api.js";
import { Stream, Media } from "./database.js";
import { configs } from "./config.js";
import { generateVideoProfiles } from "./profiles.js";
import dotenv from 'dotenv';

dotenv.config();

export let streams = {};
export async function servePlaylist(res, req) {
    // Verify url structure
    const urlParts = decodeURIComponent(req.url).substring(1).split("/");
    if(urlParts.length !== 3) {
        errorResponse(res, 400, "Invalid url structure");
        return;
    }

    // Retrieve stream code
    const streamCode = urlParts[1];
    if(!Object.keys(streams).includes(streamCode)) {
        const stream = await Stream.findOne({
            where: {
                code: streamCode,
                alive: 1
            },
            include: {
                model: Media,
                as: 'Media'
            },
        });
    
        if(stream === null){
            errorResponse(res, 400, "Stream not found");
            return;
        }
    
        let profiles = generateVideoProfiles(stream.Media.quality);
        streams[streamCode] = {
            "sourcePath": stream.Media.path.replace('/var/www/html/', './'),
            "duration": stream.Media.duration,
            "profiles": profiles
        };
    }

    if (urlParts[2] === 'master.m3u8') {
        let playlist = "#EXTM3U\n#EXT-X-VERSION:3\n";
        for (let profileName in streams[streamCode]['profiles']) {
            playlist += `#EXT-X-STREAM-INF:BANDWIDTH=${streams[streamCode]['profiles'][profileName]["bitrate"].replace("k", "000")},RESOLUTION=${streams[streamCode]['profiles'][profileName]["scale"].replace(":", "x")}\n`;
            playlist += process.env.MEDIA_SERVER_URL + `/playlists/${streamCode}/${profileName}.m3u8\n`;
        }
        playlist += "#EXT-X-ENDLIST\n";

        res.setHeader("Content-Type", "application/vnd.apple.mpegurl");
        res.end(playlist);
    } else {
        // Verify if stream profile exists
        const profile = urlParts[2].replace('.m3u8', '');
        if (!Object.keys(streams[streamCode]['profiles']).includes(profile)) {
            errorResponse(res, 400, "Stream profile not found");
            return;
        }

        let playlist = "#EXTM3U\n";
        playlist += "#EXT-X-VERSION:4\n";
        playlist += "#EXT-X-PLAYLIST-TYPE:VOD\n";
        playlist += "#EXT-X-TARGETDURATION:" + configs['segmentDuration'] + "\n";
        playlist += "#EXT-X-MEDIA-SEQUENCE:0\n";

        // Create segments with the right segment duration
        let i = 0;
        let j = streams[streamCode]['duration'];
        while (j > configs['segmentDuration']) {
            playlist += `#EXTINF:${configs['segmentDuration']}, no desc\n`;
            playlist += process.env.MEDIA_SERVER_URL + `/segments/${streamCode}/${profile}/${i}.ts\n`;
            j -= configs['segmentDuration'];
            i++;
        }

        // If there is some duration over create the last segment
        if (j > 1) {
          playlist += `#EXTINF:${Math.floor(j * 1000) / 1000}, no desc\n`;
          playlist += process.env.MEDIA_SERVER_URL + `/segments/${streamCode}/${profile}/${i}.ts\n`;
        }

        playlist += "#EXT-X-ENDLIST\n";
        res.setHeader("Content-Type", "application/vnd.apple.mpegurl");
        res.end(playlist);
    }
}