<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\JournalsService;

class JournalsController{

    private JournalsService $service;

    public function __construct()
    {
        $this->service =  new JournalsService();
    }

}