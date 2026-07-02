<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\CategoryService;
use App\Services\DepartmentService;

class CategoryController{

    private CategoryService $service;
   
    public function __construct()
    {
        $this->service =  new CategoryService();      
    }

    public function index(){
        $cursor = isset($_GET['cursor']) ? (int) $_GET['cursor'] : null;
        $direction = $_GET['direction'] ?? 'next';

        $filters = [
            'search' => $_GET['search'] ?? null,          
            'id' => $_GET['id'] ?? null,          
        ];

        //validate this array
        $result = $this->service->paginateOrders($cursor, $direction, $filters);
        Response::success($result, "categories information");
    }

    

    public function show(string $id){
        $id = RequestValidator::parseId($id);

        $categories = $this->service->getById((int)$id);
        if(!$categories) Response::error(404, 'Categories not found');
        Response::success($categories, "Categories found");
    }

    public function store(){
        $data = RequestValidator::validate([ 
            'category' => 'required|min:1',            
        ]);
            
        $data = RequestValidator::sanitize($data); 
        $created = $this->service->create($data);
        Response::success($created, "category saved");
    }

    public function update(string $id){
        $id = RequestValidator::parseId($id);

        $data = RequestValidator::validate([ 
            'category' => 'required|min:1',            
        ]);
        
        $data = RequestValidator::sanitize($data);         
        $update = $this->service->update((int)$id, $data);
        Response::success($update, "category updated");
    }

    public function destroy(string $id){
        $id = RequestValidator::parseId($id);
        $delete = $this->service->delete((int)$id);       
        Response::success($delete, "category deleted");
    }

}