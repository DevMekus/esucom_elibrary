<?php

use App\Controllers\AuthController;
use App\Controllers\DatabasesController;
use App\Controllers\EbookController;
use App\Controllers\JournalsController;
use App\Controllers\OpacController;
use App\Controllers\SchoolController;
use App\Routes\Router;
use App\Middleware\GuestOnlyMiddleware;


$auth = new AuthController();
$ebook = new EbookController();
$ejournal = new JournalsController();
$database = new DatabasesController();
$opac = new OpacController();
$school = new SchoolController();


Router::group('v1', function () use (
    $auth,
    $ebook,
    $ejournal,
    $database,
    $opac,
    $school
) {

    #Auth Routes
    Router::add('POST', '/auth/login', [$auth, 'login']);
    Router::add('POST', '/auth/logout/{userid}', [$auth, 'logOut']);
    Router::add('POST', '/auth/recover', [$auth, 'recoverAccount']);
    Router::add('POST', '/auth/reset', [$auth, 'resetPassword']);
   
    #Ebook Routes
    Router::add('GET', '/ebook', [$ebook, 'index']); 

    #ejournal Routes
    Router::add('GET', '/ejournal', [$ejournal, 'index']); 

    #database Routes
    Router::add('GET', '/database', [$database, 'index']); 
    
    #database Routes
    Router::add('GET', '/catalog', [$opac, 'index']); 

    #School Routes
    Router::add('GET', '/school', [$school, 'school']); 

}, [GuestOnlyMiddleware::class]);