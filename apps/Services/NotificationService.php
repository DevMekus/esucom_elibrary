<?php
namespace App\Services;
use App\Exceptions\ValidationFailedException;
use App\Repositories\NotificationRepository;
use configs\Database;
use PDO;

class NotificationService{
    private NotificationRepository $repo;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->repo =  new NotificationRepository($this->db);
    }
}