<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/siteConfig.php';


header("Content-type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/configs/env.php';
require_once ROOT_PATH . '/configs/ErrorHandler.php';
require_once ROOT_PATH . '/Routes/api.php';

use configs\ErrorHandler;

set_error_handler([ErrorHandler::class, 'handleError']);
set_exception_handler([ErrorHandler::class, 'handleException']);
