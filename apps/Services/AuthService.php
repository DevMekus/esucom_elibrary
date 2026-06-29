<?php
namespace App\Services;

use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationFailedException;
use App\Repositories\UserRepository;
use PDO;
use configs\Database;
use App\Services\LogService;
use App\Services\UserService;
use App\Utils\Utility;

class AuthService {
    private UserRepository $repo;
    private PDO $db;
    private LogService $logging;
    private UserService $userService;

    public function __construct() {
        $this->db = Database::connect();
        $this->repo = new UserRepository($this->db);  
        $this->logging = new LogService($this->db);
        $this->userService = new UserService();      
    }

    public function login(string $authenticate, string $password){
       
        try {

            $this->db->beginTransaction();            
            
            $getCursor = $this->repo->paginateOrders(null, 'next', ['authenticate' => $authenticate]);           

            if(!$getCursor || count($getCursor['data']) == 0){
                return null;
            }

            $user = $getCursor['data'][0];

            if (!$this->userService->verifyPassword($password, $user['p_sx'])){
                return null;
            }

            if ($user['status'] !== 'active'){
                throw new ValidationFailedException('Account not active');
            }

            //Create and save new session
            $token = $this->userService->generateSessionToken($user);

            if(!$token){
                throw new ValidationFailedException('session token failed');            
            } 

            //log
            $this->logging->create([              
                'type' => 'authentication',
                'title' => "user logged in successfully",
                'status' => true,
                'userid'=> $user['userid']
            ]);

            $this->db->commit();

            return [
                "user" => [
                    'userid' => $user['userid'],
                    'fullname' => $user['fullname'],
                    'email_address' => $user['email_address'],
                    'phone' => $user['phone'],
                    'address' => $user['address'],
                ],
                "token" => $token,
                "refresh" => "",
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
           $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Service error while logging in: " . $originalMessage,
                0,
                $e
            );
        }
       
    }

    public function logout(string $userid):bool{
        //destroy session, clear the session table
        header('Authorization: Bearer null');        

        $getCursor = $this->repo->paginateOrders(null, 'next', ['search' => $userid]);
        if(!$getCursor || count($getCursor['data']) == 0){
            return false;
        }

        $user = $getCursor['data'][0];

        try {
            $this->db->beginTransaction();
                
            $this->repo->destroySession($userid);

                $this->logging->create([                   
                    'type' => 'logout',
                    'title' => 'logout successful',
                    'status' => true,
                    'userid' => $user['userid']
                ]);

            $this->db->commit();

            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException("Service error while logging out user", 0, $e);
        }

        
        
    }

    public function requestPasswordReset(string $email){

        $getCursor = $this->repo->paginateOrders(null, 'next', ['search' => $email]);
        if(!$getCursor || count($getCursor['data']) == 0){
            return false;
        }

        $user = $getCursor['data'][0];

        $reset = $this->userService->requestPasswordReset($user);
        
        if (!$reset) return false;

        return EmailServices::passwordResetEmail([
            'email_address'=> $user['email_address'],
            'reset_token' => $reset['reset_token'],
            'fullname' => $user['fullname'],
        ]);
    }

    public function resetPassword(string $token, string $newPassword){
    
        $getCursor = $this->repo->paginateOrders(null, 'next', ['token' => $token]);
      
        if(!$getCursor || count($getCursor['data']) == 0){
            return false;
        }

        $user = $getCursor['data'][0];

        try {            
            $this->db->beginTransaction();

            $this->userService->changePassword($user, $newPassword);
            
            $this->logging->create([                
                'type' => 'authentication',
                'title' => 'Password reset successful',
                'status' => true,
                'userid' => $user['userid']
            ]);
            $this->db->commit();

            return true;

        
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException("Service error while login user", 0, $e);
        }
    }



    
}