<?php
namespace App\Services;
use App\Exceptions\ValidationFailedException;
use App\Repositories\DatabaseRespository;
use configs\Database;
use PDO;

class DatabaseService{
    private DatabaseRespository $repo;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->repo =  new DatabaseRespository($this->db);
    }
}