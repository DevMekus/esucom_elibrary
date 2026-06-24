<?php
namespace App\Services;
use App\Exceptions\ValidationFailedException;
use App\Repositories\OpacRepository;
use configs\Database;
use PDO;

class OpacService{
    private OpacRepository $repo;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->repo =  new OpacRepository($this->db);
    }
}