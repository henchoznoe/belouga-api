<?php

use HTTP\HTTPHandlers;
use HTTP\HTTPResponses;
use Dotenv\Dotenv;

const ROOT = __DIR__;

require_once ROOT . "/vendor/autoload.php";
require_once ROOT . "/http/index.php";
require_once ROOT . "/wrk/index.php";
require_once ROOT . "/constants/db-queries/index.php";

// Load environment variables
Dotenv::createImmutable(ROOT)->load();

// Set headers
header('Access-Control-Allow-Origin: ' . $_ENV['HEADERS_CORS_HOST']);
header('Access-Control-Allow-Headers: ' . $_ENV['HEADERS_CORS_HEADERS']);
header('Access-Control-Allow-Methods: ' . $_ENV['HEADERS_CORS_HTTP_METHODS']);
header('Access-Control-Max-Age:' . $_ENV['HEADERS_CORS_MAX_AGE']);
header('Content-Type: ' . $_ENV['HEADERS_CONTENT_TYPE']);

date_default_timezone_set('Europe/Zurich');

// Handle HTTP requests
if ( isset($_SERVER["REQUEST_METHOD"]) ) {
    $http = new HTTPHandlers();
    switch ( $_SERVER["REQUEST_METHOD"] ) {
        case "GET":
            $http->GET();
            break;
        case "POST":
            $http->POST();
            break;
        case "PATCH":
            $http->PATCH();
            break;
        case "DELETE":
            $http->DELETE();
            break;
        case "OPTIONS":
            header("HTTP/1.1 200 OK");
            http_response_code(204);
            break;
        default:
            HTTPResponses::error(405, "Méthode HTTP non autorisée");
            break;
    }
} else {
    HTTPResponses::error(405, "Méthode HTTP non spécifiée");
}
