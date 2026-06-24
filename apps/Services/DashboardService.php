<?php
namespace App\Services;
use App\Exceptions\ValidationFailedException;

use configs\Database;
use PDO;

class DashboardService{
   
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
      
    }
}