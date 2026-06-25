<?php
namespace App\Services;

use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ResourceNotFoundException;
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

    public function paginateOrders(?int $cursor, string $direction = 'next', $filters ): array{
        //validate direction
        if (!in_array($direction, ['next', 'prev'])){
            $direction = 'next';
        }

        $data = $this->repo->paginateOrders($cursor, $direction, $filters);
        // optional: add metadata layer (useful for frontend)

        return $data;
       
    }

    private function validate(array $data){
        if (!isset($data['title'])){
            throw new ValidationFailedException('Title is required');
        }

        if ($this->repo->exist($data['title'])){
            throw new ResourceAlreadyExistsException("Notification already Exists");
        }
    }

    
    public function create(array $data){    
        $this->validate($data);

        return $this->repo->create($data);
    }

    public function update(int $id, array $data){
        if (!isset($id)){
            throw new ValidationFailedException('Notification Id required');
        }
        $getCursor = $this->paginateOrders(null, 'next', ['id' => $id]);
         
        if(!$getCursor || count($getCursor['data']) == 0){
            throw new ResourceNotFoundException("Notification not found");
        }

        $noticePrev = $getCursor['data'][0];

        return $this->repo->update($noticePrev , $data);
    }

    public function delete(int $id){
        if (!isset($id)){
            throw new ValidationFailedException('Notification Id required');
        }

        return $this->repo->delete((int)$id);
    }
}