<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\EbookService;

class EbookController{

    private EbookService $service;

    public function __construct()
    {
        $this->service =  new EbookService();
    }

}