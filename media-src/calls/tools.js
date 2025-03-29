export function crazyNumber(num) {
    return String(num) + ".000000";
}

export function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}