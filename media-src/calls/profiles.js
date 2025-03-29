import { configs } from "./config.js";

function gcd(a, b) {
    while (b !== 0) {
        let temp = b;
        b = a % b;
        a = temp;
    }
    return a;
}

function adjustToEven(value) {
    return value % 2 === 0 ? value : value - 1;
}

export function generateVideoProfiles(inputResolution) {
    // Parse original dimensions
    const [originalWidth, originalHeight] = inputResolution.split('x').map(Number);

    // Find the original aspect ratio
    const divisor = gcd(originalWidth, originalHeight);
    const aspectRatioWidth = originalWidth / divisor;
    const aspectRatioHeight = originalHeight / divisor;

    // Generate profiles
    const profiles = {};
    const standardProfiles = configs['standardProfiles'];
    const adjustedOriginalWidth = adjustToEven(originalWidth);
    const adjustedOriginalHeight = adjustToEven(originalHeight);

    // Track which dimensions we've already added to avoid duplicates
    const addedDimensions = new Set();

    // Add the original dimensions first
    profiles["original"] = {
        scale: `${adjustedOriginalWidth}:${adjustedOriginalHeight}`,
        bitrate: `${Math.round(Math.max(8000, originalWidth * originalHeight * 0.00005))}k`
    };
    addedDimensions.add(`${adjustedOriginalWidth}:${adjustedOriginalHeight}`);

    // Generate profiles for standard resolutions
    for (const profile of standardProfiles) {
        let profileHeight, profileWidth;

        if (originalHeight <= profile.maxHeight) {
            // Original is already smaller than this profile's height
            // Skip this profile if we've already added the original dimensions
            if (addedDimensions.has(`${adjustedOriginalWidth}:${adjustedOriginalHeight}`)) {
                continue;
            }
            profileHeight = adjustedOriginalHeight;
            profileWidth = adjustedOriginalWidth;
        } else {
            // Scale down to the profile height
            profileHeight = profile.maxHeight;
            profileWidth = Math.round((profileHeight / aspectRatioHeight) * aspectRatioWidth);
            // Ensure dimensions are even
            profileWidth = adjustToEven(profileWidth);
            profileHeight = adjustToEven(profileHeight);
        }

        const dimensionKey = `${profileWidth}:${profileHeight}`;

        // Skip if we've already added a profile with these dimensions
        if (addedDimensions.has(dimensionKey)) {
            continue;
        }

        profiles[profile.name] = {
            scale: dimensionKey,
            bitrate: profile.bitrate
        };

        addedDimensions.add(dimensionKey);
    }

    return profiles;
}