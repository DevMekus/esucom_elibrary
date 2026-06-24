<?php

use App\Controllers\AuthController;
use App\Routes\Router;
use App\Middleware\GuestOnlyMiddleware;

$auth = new AuthController();


Router::group('v1', function () use ($auth) {
   
   

}, [GuestOnlyMiddleware::class]);