<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\DatabaseService;

class DatabasesController{

    private DatabaseService $service;

    public function __construct()
    {
        $this->service =  new DatabaseService();
    }

}