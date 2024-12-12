<?php

namespace HTTP;

/**
 * Class HTTPResponses
 * @package HTTP
 * @author Noé Henchoz
 * @date 2024-12
 */
class HTTPResponses {

    /**
     * Send a response to the client. The response is in JSON format and contains a status, a message and some data.
     * @param int $code HTTP status code
     * @param bool $status true if the request was successful, false otherwise
     * @param string $message message to be displayed
     * @param array|null $data data to be sent or null if there is no data
     * @return void nothing is returned
     */
    public static function sendResponse(int $code, bool $status, string $message, array $data = null): void {
        $response = ['success' => $status, 'message' => $message, 'data' => $data ?? []];
        http_response_code($code);
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Send a successful response to the client. The response is in JSON format and contains a status, a message and some data.
     * @param string $message message to be displayed
     * @param array|null $data data to be sent or null if there is no data
     * @return void nothing is returned
     */
    public static function success(string $message, array $data = null): void {
        self::sendResponse(200, true, $message, $data);
    }

    /**
     * Send an error response to the client. The response is in JSON format and contains a status, a message and some data.
     * @param int $code HTTP status code
     * @param string $message message to be displayed
     * @return void nothing is returned
     */
    public static function error(int $code, string $message): void {
        self::sendResponse($code, false, $message);
    }

}
