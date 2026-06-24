
<?php
// /Router/api.php
use App\Routes\Router;
use App\Utils\Utility;
use App\Utils\ExceptionHandler;

foreach (glob(__DIR__ . "/*.php") as $file) {
    if ($file !== __FILE__) {
        require_once $file;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip /api from the URI if it's part of the base path
$cleanedUri = preg_replace('#^' . Utility::$API_ROUTE . '#', '', $uri);

try {
    Router::dispatch($method, $cleanedUri);
} catch (\Throwable $th) {
   ExceptionHandler::handle($th);
}
