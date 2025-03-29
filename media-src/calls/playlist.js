import { errorResponse, response } from "./api.js";
import { Stream, Media } from "./database.js";
import { configs } from "./config.js";
import { generateVideoProfiles } from "./profiles.js";

export async function servePlaylist(res, req) {
    // Parse request data
    const urlParts = decodeURIComponent(req.url).substring(1).split("/");
    if(urlParts.length !== 3) {
        errorResponse(res, 400, "Invalid url structure");
        return;
    }

    // Retrieve stream code
    const streamCode = urlParts[1];
    const stream = await Stream.findOne({
        where: {
            code: streamCode,
        },
        include: {
            model: Media,
            as: 'Media'
        },
    });

    if(stream === null){
        errorResponse(res, 404, "Stream not found");
        return;
    }

    // Retrieve viewing profiles
    let localProfiles = generateVideoProfiles(stream.Media.quality);

    response(res, localProfiles);
}