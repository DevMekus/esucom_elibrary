<?php

use App\Controllers\CategoryController;
use App\Controllers\DepartmentController;
use App\Routes\Router;
use App\Middleware\AdminOnlyMiddleware;



$categories = new CategoryController();
$department = new DepartmentController();


Router::group('v1/admin', function () use ($categories, $department ) {
    
    #Category Routes
    Router::add('GET', '/category', [$categories, 'index']); 
    Router::add('POST', '/category', [$categories, 'store']); 
    Router::add('PATCH', '/category/{id}', [$categories, 'update']); 
    Router::add('DELETE', '/category/{id}', [$categories, 'destroy']); 
    
    #department Routes
    Router::add('GET', '/department', [$department, 'index']); 
    Router::add('POST', '/department', [$department, 'store']); 
    Router::add('PATCH', '/department/{id}', [$department, 'update']); 
    Router::add('DELETE', '/department/{id}', [$department, 'destroy']); 
   

}, [AdminOnlyMiddleware::class]);