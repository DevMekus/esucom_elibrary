<?php
namespace App\Services;
use App\Exceptions\ValidationFailedException;
use App\Repositories\EbookRepository;
use configs\Database;
use PDO;

class EbookService{
    private EbookRepository $repo;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->repo =  new EbookRepository($this->db);
    }
}