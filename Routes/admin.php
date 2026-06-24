<?php


use App\Controllers\UserController;
use App\Routes\Router;
use App\Middleware\AdminOnlyMiddleware;


$user = new UserController();


Router::group('v1', function () use ($user) {
   
   

}, [AdminOnlyMiddleware::class]);