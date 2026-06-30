<?php
namespace App\Services;

use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ResourceNotFoundException;
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
        if (!$data['title'] || !$data['department_id']) {
            throw new ValidationFailedException("Journal title and department ID required");
        }

        if ($this->repo->exist($data['department_id'], $data['title'])){
            throw new ResourceAlreadyExistsException("Journal already Exists");           
        }
    }

    public function create(array $data){    
        $this->validate($data);      

        return $this->repo->create($data);
    }

    public function update(string $id, array $data){
        if (!isset($id)){
            throw new ValidationFailedException('Database Id required');
        }
        $getCursor = $this->paginateOrders(null, 'next', ['rowid' => (int)$id]);
         
        if(!$getCursor || count($getCursor['data']) == 0){
            throw new ResourceNotFoundException("Journal information failed to fetch");
        }

        $journal = $getCursor['data'][0];

        $newJournal = [
            'id' => $id,
            'url' => $data['url'] ?? $journal['url'],
            'title' => $data['title'] ?? $journal['title'],
            'department_id' => $data['department_id'] ?? $journal['department_id'],
        ];

        return $this->repo->update($newJournal);
    }

    public function delete(string $id){
        if (!isset($id)){
            throw new ValidationFailedException('Journal Id required');
        }

        return $this->repo->delete((int)$id);
    }


}