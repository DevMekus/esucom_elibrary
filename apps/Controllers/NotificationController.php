<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\NotificationService;

class NotificationController{

    private NotificationService $service;

    public function __construct()
    {
        $this->service =  new NotificationService();
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
        Response::success($result, "Notification information");
    }

    public function store(){
        $data = RequestValidator::validate([
            'notice_type' => 'required|min:3',
            'send_to' => 'required|min:3',
            'title' => 'required|min:6',
            'message' => 'required|min:6',
            'created_at' => 'required'            
        ]);

        $data = RequestValidator::sanitize($data);        

        $created = $this->service->create($data);

        Response::success($created, "Notification saved");
    }

    public function update(string $id){
        $id = RequestValidator::parseId($id);

       $data = RequestValidator::validate([
           'notice_type' => 'required|min:3',
            'send_to' => 'required|min:3',
            'title' => 'required|min:6',
            'message' => 'required|min:6',            
        ]);

        $data = RequestValidator::sanitize($data);         
        $update = $this->service->update((int)$id, $data);
        Response::success($update, "Notification updated");
    }

    public function destroy(string $id){
        $id = RequestValidator::parseId($id);
        $delete = $this->service->delete((int)$id);       
        Response::success($delete, "Notification deleted");
    }

}