<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\OpacService;

class OpacController{

    private OpacService $service;

    public function __construct()
    {
        $this->service =  new OpacService();
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
        Response::success($result, "Opac information");
    }

    public function store(){
        $data = RequestValidator::validate([
            'author' => 'required|min:3',
            'title' => 'required|min:3',            
            'publisher' => 'required|min:1',
            'subject_id' => 'required|min:1',
            'publication_place' => 'required|min:1',
            'date_of_publication' => 'required|min:1',
            'call_number' => 'required|min:1',
            'serial_number' => 'required|min:1',
            'shelve_number' => 'required|min:1',
            
        ]);
            
        $data = RequestValidator::sanitize($data);         

        $created = $this->service->create($data);

        Response::success($created, "Opac saved");
    }

    public function update(string $id){
        $id = RequestValidator::parseId($id);

        $data = RequestValidator::validate([
            'author' => 'required|min:3',
            'title' => 'required|min:3',            
            'publisher' => 'required|min:1',
            'subject_id' => 'required|min:1',
            'publication_place' => 'required|min:1',
            'date_of_publication' => 'required|min:1',
            'call_number' => 'required|min:1',
            'serial_number' => 'required|min:1',
            'shelve_number' => 'required|min:1',
            
        ]);
        
        $data = RequestValidator::sanitize($data);         
        $update = $this->service->update((int)$id, $data);
        Response::success($update, "Opac updated");
    }

    public function destroy(string $id){
        $id = RequestValidator::parseId($id);
        $delete = $this->service->delete((int)$id);       
        Response::success($delete, "Opac deleted");
    }

}