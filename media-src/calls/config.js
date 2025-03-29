import { StreamConfig } from "./database.js";

function configToString(name, value) {
    if(name === 'standardProfiles') {
        return JSON.stringify(value);
    } else {
        return String(value)
    }
}

function stringToConfig(name, value) {
    if(name === 'standardProfiles') {
        return JSON.parse(value);
    } else if(name === 'segmentDuration') {
        return Number(value)
    }
}

export let configs = {};
export async function retrieveConfig() {
    let names = ['segmentDuration', 'standardProfiles'];

    for(let i = 0; i < names.length; i++) {
        const config = await StreamConfig.findOne({
            where: {
                name: names[i],
            }
        });

        if(config) {
            configs[names[i]] = stringToConfig(names[i], config.value);
        } else {
            // Find config not exist create one
            let value;
            if(names[i] == 'segmentDuration') {
                value = 4;
            }else if(names[i] == 'standardProfiles') {
                value = [
                    { name: '1080p', maxHeight: 1080, bitrate: '8000k' },
                    { name: '720p', maxHeight: 720, bitrate: '5000k' },
                    { name: '480p', maxHeight: 480, bitrate: '2500k' },
                    { name: '360p', maxHeight: 360, bitrate: '2000k' }
                ];
            }

            await StreamConfig.create({
                name: names[i],
                value: configToString(names[i], value),
            });
        }
    }
}