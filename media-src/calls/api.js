export function errorResponse(res, errorCode, message) {
    res.setHeader("Content-Type", "application/json");
    res.statusCode = errorCode;
    res.end(JSON.stringify({
        "status": "error",
        "message": message,
    }));
}

export function response(res, data) {
    res.setHeader("Content-Type", "application/json");
    res.end(JSON.stringify(data));
}
