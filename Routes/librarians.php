<?php

use App\Controllers\EbookController;
use App\Controllers\UserController;
use App\Routes\Router;
use App\Middleware\UserOnlyMiddleware;


$user = new UserController();

$ebook = new EbookController();

Router::group('v1', function () use ($user, $ebook) {
   
    #User Routes
    Router::add('GET', '/users', [$user, 'index']);
    Router::add('GET', '/users/{id}', [$user, 'show']);
    Router::add('DELETE', '/users/{id}', [$user, 'destroy']); #Admin deletes, no password
    Router::add('POST', '/users/{id}', [$user, 'destroyProfile']); #user deletes with password
    Router::add('POST', '/users/update/{id}', [$user, 'update']);

    #Ebook Routes
    Router::add('POST', '/ebook', [$ebook, 'store']); 
    Router::add('POST', '/update/ebook/{id}', [$ebook, 'update']); 
    Router::add('DELETE', '/ebook/{id}', [$ebook, 'destroy']); 

}, [UserOnlyMiddleware::class]);