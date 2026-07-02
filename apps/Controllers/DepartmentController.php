<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\DepartmentService;

class DepartmentController{

    private DepartmentService $service;
   
    public function __construct()
    {
        $this->service =  new DepartmentService();      
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
        Response::success($result, "departments information");
    }

    

    public function show(string $id){
        $id = RequestValidator::parseId($id);

        $categories = $this->service->getById((int)$id);
        if(!$categories) Response::error(404, 'department not found');
        Response::success($categories, "department found");
    }

    public function store(){
        $data = RequestValidator::validate([ 
            'department_name' => 'required|min:1',            
        ]);
            
        $data = RequestValidator::sanitize($data); 
        $created = $this->service->create($data);
        Response::success($created, "department saved");
    }

    public function update(string $id){
        $id = RequestValidator::parseId($id);

        $data = RequestValidator::validate([ 
            'department_name' => 'required|min:1',            
        ]);
        
        $data = RequestValidator::sanitize($data);         
        $update = $this->service->update((int)$id, $data);
        Response::success($update, "department updated");
    }

    public function destroy(string $id){
        $id = RequestValidator::parseId($id);
        $delete = $this->service->delete((int)$id);       
        Response::success($delete, "department deleted");
    }

}