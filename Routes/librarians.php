<?php


use App\Controllers\UserController;
use App\Routes\Router;
use App\Middleware\UserOnlyMiddleware;


$user = new UserController();


Router::group('v1', function () use ($user) {
   
   

}, [UserOnlyMiddleware::class]);