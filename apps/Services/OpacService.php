<?php
namespace App\Services;

use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ResourceNotFoundException;
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
        if (!isset($data['title'], $data['author'])){
            throw new ValidationFailedException('Opac Information missing');
        }

        if ($this->repo->exist($data['title'], $data['author'])){
            throw new ResourceAlreadyExistsException("Opac already Exists");
        }
    }

    public function create(array $data){    
        $this->validate($data);

        return $this->repo->create($data);
    }

    public function update(int $id, array $data){
        if (!isset($id)){
            throw new ValidationFailedException('Opac Id required');
        }
        $getCursor = $this->paginateOrders(null, 'next', ['id' => $id]);
         
        if(!$getCursor || count($getCursor['data']) == 0){
            throw new ResourceNotFoundException("Opac information failed to fetch");
        }

        $prevOpac = $getCursor['data'][0];

        return $this->repo->update($prevOpac , $data);
    }

    public function delete(int $id){
        if (!isset($id)){
            throw new ValidationFailedException('Opac Id required');
        }

        return $this->repo->delete((int)$id);
    }
}