<?php
namespace App\Services;
use App\Exceptions\ValidationFailedException;
use App\Repositories\JournalsRepository;
use configs\Database;
use PDO;

class JournalsService{
    private JournalsRepository $repo;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->repo =  new JournalsRepository($this->db);
    }
}