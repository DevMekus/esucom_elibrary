<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\DashboardService;

class DashboardsController{

    private DashboardService $service;

    public function __construct()
    {
        $this->service =  new DashboardService();
    }

    

}