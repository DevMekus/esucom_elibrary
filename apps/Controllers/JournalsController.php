<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\JournalsService;

class JournalsController{

    private JournalsService $service;

    public function __construct()
    {
        $this->service =  new JournalsService();
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
        Response::success($result, "Ebook information");
    }

    public function store(){
        $data = RequestValidator::validate([
            'title' => 'required|min:3',          
            'department_id' => 'required|min:1',            
            'access_url' => 'required|min:1',
        ]);
        $data = RequestValidator::sanitize($data);        

        $created = $this->service->create($data);

        Response::success($created, "Ebook saved");
    }

    public function update(string $id){
        $id = RequestValidator::parseId($id);

        $data = RequestValidator::validate([
            'title' => 'required|min:3',          
            'department_id' => 'required|min:1',            
            'access_url' => 'required|min:1',
        ]);
        $data = RequestValidator::sanitize($data);         
        $update = $this->service->update((int)$id, $data);
        Response::success($update, "Ebook updated");
    }

    public function destroy(string $id){
        $id = RequestValidator::parseId($id);
        $delete = $this->service->delete((int)$id);       
        Response::success($delete, "Ebook deleted");
    }

}