<?php
namespace App\Services;

use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationFailedException;
use App\Repositories\DepartmentRepository;
use configs\Database;
use PDO;

class DepartmentService{
    private DepartmentRepository $repo;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->repo =  new DepartmentRepository($this->db);
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

    public function getAll(){
        $department = $this->repo->findAll();
        if(!$department || count($department) == 0) return null;

        return $department;
    }

    public function getById(int $id){
        $department = $this->repo->findById($id);
        if(!$department || count($department) == 0) return null;

        return $department;
    }

    private function validate(array $data){
        if (!isset($data['department_name'])){
            throw new ValidationFailedException('department name required');
        }

        if ($this->repo->exist($data['department_name'])){
            throw new ResourceAlreadyExistsException("department already Exists");
        }
    }

    public function create(array $data){
        $this->validate($data);

        return $this->repo->create($data);
    }

    public function update(int $id, array $data){
        if (!isset($id)){
            throw new ValidationFailedException('department Id required');
        }
        $getdepartment = $this->repo->findById($id);
         
        if(!$getdepartment || count($getdepartment) == 0){
            throw new ResourceNotFoundException("department information failed to fetch");
        }

        $department = $getdepartment[0];

        return $this->repo->update($department , $data);
    }

    public function delete(int $id){
        if (!isset($id)){
            throw new ValidationFailedException('department Id required');
        }

        return $this->repo->delete((int)$id);
    }
}