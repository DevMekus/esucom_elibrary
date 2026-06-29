<?php
namespace App\Services;

use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationFailedException;
use App\Repositories\CategoriesRepository;
use configs\Database;
use PDO;

class CategoryService{
    private CategoriesRepository $repo;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->repo =  new CategoriesRepository($this->db);
    }

    public function getAll(){
        $categories = $this->repo->findAll();
        if(!$categories || count($categories) == 0) return null;

        return $categories;
    }

    private function validate(array $data){
        if (!isset($data['category'])){
            throw new ValidationFailedException('category name required');
        }

        if ($this->repo->exist($data['category'])){
            throw new ResourceAlreadyExistsException("category already Exists");
        }
    }

    public function create(array $data){
        $this->validate($data);

        return $this->repo->create($data);
    }

    public function update(int $id, array $data){
        if (!isset($id)){
            throw new ValidationFailedException('Database Id required');
        }
        $getCategory = $this->repo->findById($id);
         
        if(!$getCategory || count($getCategory) == 0){
            throw new ResourceNotFoundException("category information failed to fetch");
        }

        $category = $getCategory[0];

        return $this->repo->update($category , $data);
    }

    public function delete(int $id){
        if (!isset($id)){
            throw new ValidationFailedException('category Id required');
        }

        return $this->repo->delete((int)$id);
    }
}