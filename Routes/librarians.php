<?php

use App\Controllers\DatabasesController;
use App\Controllers\EbookController;
use App\Controllers\JournalsController;
use App\Controllers\UserController;
use App\Routes\Router;
use App\Middleware\UserOnlyMiddleware;


$user = new UserController();
$ebook = new EbookController();
$ejournal = new JournalsController();
$database = new DatabasesController();

Router::group('v1', function () use ($user, $ebook, $ejournal, $database) {
   
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


    #Journal Routes
    Router::add('POST', '/ejournal/new', [$ejournal, 'store']); 
    Router::add('PATCH', '/ejournal/update/{id}', [$ejournal, 'update']); 
    Router::add('DELETE', '/ejournal/{id}', [$ejournal, 'destroy']); 

    #Database Routes
    Router::add('POST', '/database/new', [$database, 'store']); 
    Router::add('PATCH', '/database/update/{id}', [$database, 'update']); 
    Router::add('DELETE', '/database/{id}', [$database, 'destroy']); 

    

}, [UserOnlyMiddleware::class]);