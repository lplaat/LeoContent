import http from "http";
import dotenv from 'dotenv';

import { errorResponse } from "./calls/api.js";
import { databaseInit } from "./calls/database.js";
import { servePlaylist } from "./calls/playlist.js";
import { retrieveConfig } from "./calls/config.js";

dotenv.config();

const server = http.createServer(async (req, res) => {
    const url = decodeURIComponent(req.url);
    const urlParts = url.substring(1).split("/");

    if (urlParts[0] === "playlist") {
        servePlaylist(res, req);
    }else {
        errorResponse(res, 404, "Route not Found!");
    }
});

(async () => {
    await databaseInit();
    await retrieveConfig();

    server.listen(process.env.MEDIA_SERVER_PORT, process.env.MEDIA_SERVER_HOST, () => {
        console.log(`Server running at ${process.env.MEDIA_SERVER_URL}`);
    });
})();