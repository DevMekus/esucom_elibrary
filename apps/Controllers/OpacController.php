<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\OpacService;

class OpacController{

    private OpacService $service;

    public function __construct()
    {
        $this->service =  new OpacService();
    }

}