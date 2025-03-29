import { v4 as uuidv4 } from 'uuid';
import fs from 'fs';
import { spawn } from "child_process";

import { streams } from "./playlist.js";
import { configs } from "./config.js";
import { errorResponse } from "./api.js";
import { crazyNumber, sleep } from "./tools.js";

let orders = [];
let doneOrders = {};

export async function provider() {
    // Create segments path
    const segmentPaths = './segments/';
    if (!fs.existsSync(segmentPaths)) {
        fs.mkdirSync(segmentPaths, { recursive: true });
    }

    // Infinite process loop
    while (true) {
        if(orders.length === 0) {
            await sleep(250);
            continue;
        }

        // Creating segment path
        const order = orders[0];
        const segmentId = uuidv4();
        const segmentPartPath = segmentPaths + `${segmentId}`;
        const segmentFullPath = `${segmentPartPath}-${order["part"]}.ts`;

        // Prepare ffmpeg command
        const startFrame = crazyNumber(order["part"] * configs['segmentDuration']);
        const endFrame = crazyNumber((order["part"] + 1) * configs['segmentDuration']);
        const ffmpegCommand = [
            "ffmpeg",
            "-ss", startFrame,
            "-i", `"${order["path"]}"`,
            "-to", endFrame,
            "-copyts",
            "-force_key_frames", endFrame,
            "-vf", `scale=${order['profile']["scale"]},format=yuv420p`,
            "-c:v", 'libx264',
            "-pix_fmt", "yuv420p",
            "-b:v", order['profile']["bitrate"],
            "-preset", 'ultrafast',
            "-map", "0:v:0",
            "-c:a", "aac",
            "-b:a", "192k",
            "-af", "aformat=channel_layouts=stereo",
            "-map", "0:a:0",
            "-f", "segment",
            "-segment_format", "mpegts",
            "-segment_times", endFrame,
            "-segment_start_number", order["part"],
            "-segment_list_type", "flat",
            "-segment_list", "pipe:1",
            "-async", "1",
            "-strict", "-2",
            `"${segmentPartPath}-%01d.ts"`,
            "-loglevel", "verbose"
        ];


        await new Promise((resolve, reject) => {
            const ffmpegProcess = spawn(ffmpegCommand.join(" "), { shell: true });

            ffmpegProcess.stderr.on('data', (data) => {
                const message = data.toString();
                console.log(message);
            });

            ffmpegProcess.on('close', (code) => {
                doneOrders[order['id']] = segmentFullPath;
                orders.shift();
                resolve();
            });

            ffmpegProcess.on('error', (err) => {
                console.error(`Failed to start ffmpeg process: ${err.message}`);
                reject(err);
            });
        });
    }
}

async function getSegment(path, profile, part) {
    // Create segment order
    const orderId = String(uuidv4());
    orders.push({
        "id": orderId,
        "profile": profile,
        "path": path,
        "part": part,
    });

    // Wait util it is complete
    return new Promise(async function (resolve) {
        while(!Object.keys(doneOrders).includes(orderId)) {
            await sleep(250);
        }

        resolve(doneOrders[orderId]);
    });
}

export async function serveSegment(res, req) {
    // Verify url structure
    const urlParts = decodeURIComponent(req.url).substring(1).split("/");
    if(urlParts.length !== 4) {
        errorResponse(res, 400, "Invalid url structure");
        return;
    }

    // Verify stream code
    const streamCode = urlParts[1];
    if (!Object.keys(streams).includes(streamCode)) {
        errorResponse(res, 400, "Stream not found");
        return;
    }
    const stream = streams[streamCode];

    // Verify profile
    const profileName = urlParts[2];
    const profiles = stream['profiles'];
    if (!Object.keys(profiles).includes(profileName)) {
        errorResponse(res, 400, "Stream profile not found");
        return;
    }
    const profile = stream['profiles'][profileName];

    // Verify part
    const part = Number(urlParts[3].split(".ts")[0]);
    if (part > Math.ceil(stream["duration"] / configs['segmentDuration']) && part < 0) {
        errorResponse(res, 400, "Part not found");
        return;
    }

    const segmentPath = await getSegment(stream['sourcePath'], profile, part);
    res.write(fs.readFileSync(segmentPath));
    res.end();
}