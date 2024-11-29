<?php

namespace HTTP;

class HTTPResponses {

    public static function sendResponse(int $code, bool $status, string $message, array $data = null): void {
        $response = ['success' => $status, 'message' => $message];
        if ( !empty($data) ) $response['data'] = $data;
        http_response_code($code);
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }

    public static function success(string $message, array $data = null): void {
        self::sendResponse(200, true, $message, $data);
    }

    public static function error(int $code, string $message): void {
        self::sendResponse($code, false, $message);
    }

}
