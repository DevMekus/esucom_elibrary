<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\NotificationService;

class NotificationController{

    private NotificationService $service;

    public function __construct()
    {
        $this->service =  new NotificationService();
    }

}