import http from "http";
import dotenv from 'dotenv';

import { errorResponse } from "./calls/api.js";
import { databaseInit } from "./calls/database.js";
import { servePlaylist } from "./calls/playlist.js";
import { serveSegment, provider } from "./calls/segments.js";
import { retrieveConfig } from "./calls/config.js";

dotenv.config();

const server = http.createServer(async (req, res) => {
    // Add CORS headers to allow all origins
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
    res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');
    
    // Route request
    const url = decodeURIComponent(req.url);
    const urlParts = url.substring(1).split("/");

    if (urlParts[0] === "playlists") {
        servePlaylist(res, req);
    }else if (urlParts[0] === "segments") {
        serveSegment(res, req);
    }else {
        errorResponse(res, 404, "Route not Found!");
    }
});

(async () => {
    await databaseInit();
    await retrieveConfig();
    provider();

    server.listen(process.env.MEDIA_SERVER_PORT, process.env.MEDIA_SERVER_HOST, () => {
        console.log(`Server running at ${process.env.MEDIA_SERVER_URL}`);
    });
})();