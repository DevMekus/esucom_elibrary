<?php

use App\Controllers\EbookController;
use App\Controllers\UserController;
use App\Routes\Router;
use App\Middleware\AdminOnlyMiddleware;


$user = new UserController();
$ebook = new EbookController();


Router::group('v1/admin', function () use ($ebook) {
    
    
   

}, [AdminOnlyMiddleware::class]);