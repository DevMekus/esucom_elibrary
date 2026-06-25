<?php
namespace App\Services;

use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ResourceNotFoundException;
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
        if (!isset($data['db_name'], $data['access_url'])){
            throw new ValidationFailedException('Database Information missing');
        }

        if ($this->repo->exist($data['db_name'], $data['access_url'])){
            throw new ResourceAlreadyExistsException("Database already Exists");
        }
    }

    
    public function create(array $data){    
        $this->validate($data);

        return $this->repo->create([
            $data['subject_id'],
            $data['db_name'],            
            $data['access_url'],
            true,
        ]);
    }

    public function update(int $id, array $data){
        if (!isset($id)){
            throw new ValidationFailedException('Database Id required');
        }
        $getCursor = $this->paginateOrders(null, 'next', ['id' => $id]);
         
        if(!$getCursor || count($getCursor['data']) == 0){
            throw new ResourceNotFoundException("Database information failed to fetch");
        }

        $database = $getCursor['data'][0];

        return $this->repo->update($database , $data);
    }

    public function delete(int $id){
        if (!isset($id)){
            throw new ValidationFailedException('Database Id required');
        }

        return $this->repo->delete((int)$id);
    }
}