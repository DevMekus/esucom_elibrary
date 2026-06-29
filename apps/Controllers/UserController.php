<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\UserService;
use App\Utils\Request;

class UserController {
    private UserService $service;
    
    public function __construct()
    {
        $this->service = new UserService();
    }


    public function index(){
        $cursor = isset($_GET['cursor']) ? (int) $_GET['cursor'] : null;
        $direction = $_GET['direction'] ?? 'next';

        $filters = [
            'search' => $_GET['search'] ?? null,          
            'id' => $_GET['id'] ?? null,
            'userid' => $_GET['userid'] ?? null,               
            'email' => $_GET['email'] ?? null,          
        ];

        //validate this array
        $result = $this->service->paginateOrders($cursor, $direction, $filters);
        Response::success($result, "users information");
    }

    public function show(string $id){
        $id = RequestValidator::parseId($id);

        $user = $this->service->getById($id);

        if(!$user) Response::error(404, "User not found");

        Response::success($user, "User information");
    }
   

    public function store(){
        $data = RequestValidator::validate([           
            'email_address' => 'required|min:1',                
        ]);
        // $data = RequestValidator::validate([], $_POST);
        $data = RequestValidator::sanitize($data);             

        $created = $this->service->create($data);

        if(!$created)Response::error(500, "An error has occurred");

        Response::success([],"User registered");       

    }

    public function update(string $id){       
        $data = RequestValidator::validate([], $_POST);
        $data = RequestValidator::sanitize($data);             

        $created = $this->service->update((int)$id, $data);

        if(!$created)Response::error(500, "An error has occurred");

        Response::success([],"User updated");    
    }

    public function destroy(string $id){
        $id = RequestValidator::parseId($id);

        $filter = [
            'id' => (int)$id,
            'userid' => null,
        ];
        
        $delete = $this->service->deleteAccount($filter, null);


        if (!$delete) Response::error(500, "An error has occurred");
        
        Response::success($delete, "User deleted");
    }


    public function destroyProfile(string $id){

        $id = RequestValidator::parseId($id);       
        
        $data = RequestValidator::validate([
            'password' => 'required|min:3',          
        ]);        
        
        $data = RequestValidator::sanitize($data);

        $filter = [
            'id' => null,
            'userid' => $id
        ];
       
        
        $delete = $this->service->deleteAccount($filter, $data['password']);

        if (!$delete) Response::error(500, "An error has occurred");
        
        Response::success($delete, "User deleted");
    }    

   
    public function guestMessaging()
    {
        $data = RequestValidator::validate([
            'name'  => 'required|min:3',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $send = $this->service->sendGuestMessage($data);
        Response::success([], "Message Sent");                
    }

    
}