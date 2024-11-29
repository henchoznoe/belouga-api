<?php

use HTTP\HTTPHandlers;
use HTTP\HTTPResponses;
use Dotenv\Dotenv;

const ROOT = __DIR__;

require_once ROOT . "/vendor/autoload.php";
require_once ROOT . "/http/index.php";
require_once ROOT . "/wrk/index.php";
require_once ROOT . "/constants/db-queries/index.php";

$dotenv = Dotenv::createImmutable(ROOT);
$dotenv->load();

header('Access-Control-Allow-Origin: ' . $_ENV['HEADERS_CORS_HOST']);
header('Access-Control-Allow-Headers: ' . $_ENV['HEADERS_CORS_HEADERS']);
header('Access-Control-Allow-Methods: ' . $_ENV['HEADERS_CORS_HTTP_METHODS']);
header('Content-Type: ' . $_ENV['HEADERS_CONTENT_TYPE']);

date_default_timezone_set('Europe/Zurich');

if ( isset($_SERVER["REQUEST_METHOD"]) ) {
    $http = new HTTPHandlers();
    switch ( $_SERVER["REQUEST_METHOD"] ) {
        case "GET":
            $http->GET();
            break;
        case "POST":
            $http->POST();
            break;
        case "PUT":
            $http->PUT();
            break;
        case "DELETE":
            $http->DELETE();
            break;
        case "OPTIONS":
            break;
        default:
            HTTPResponses::error(405, "Méthode HTTP non autorisée");
            break;
    }
} else {
    HTTPResponses::error(405, "Méthode HTTP non spécifiée");
}
