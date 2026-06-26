<?php

use App\Controllers\AuthController;
use App\Controllers\EbookController;
use App\Controllers\JournalsController;
use App\Routes\Router;
use App\Middleware\GuestOnlyMiddleware;

$auth = new AuthController();
$ebook = new EbookController();
$ejournal = new JournalsController();


Router::group('v1', function () use (
    $auth,
    $ebook,
    $ejournal
) {
   
    #Ebook Routes
    Router::add('GET', '/ebook', [$ebook, 'index']); 

    #Jiurnal Routes
    Router::add('GET', '/ejournal', [$ejournal, 'index']); 

}, [GuestOnlyMiddleware::class]);