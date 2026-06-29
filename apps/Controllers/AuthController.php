<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\AuthService;
use App\Services\UserService;

class AuthController {
    private AuthService $authService;
   

    public function __construct()
    {
        $this->authService = new AuthService();      
    }

    public function login(){
        $data = RequestValidator::validate([
            'email_address' => 'required|min:3',
            'user_password' => 'required|min:3',
        ]);
            
        $data = RequestValidator::sanitize($data);

        $loggedIn = $this->authService->login($data['email_address'], $data['user_password']);
        if (!$loggedIn) Response::error(401, "Invalid login credentials");

        Response::success($loggedIn, "logging successful");
    }

    public function logOut(string $userid){
        
        $userid = RequestValidator::parseId($userid);
        
        
        $loggedOut = $this->authService->logout($userid);

        if(!$loggedOut) Response::error(500, "Logout failed");

        Response::success([], "Logout successful");
    }

    public function recoverAccount(){
        $data = RequestValidator::validate([
            'email_address' => 'required|email',
        ]);

        $data = RequestValidator::sanitize($data);
        
        $resetToken = $this->authService->requestPasswordReset($data['email_address']) ;

        if (!$resetToken)Response::error(500, "Account recovery failed");

        Response::success([], "A reset link has been sent to your registered email.");
    }

    public function resetPassword(){
        $data = RequestValidator::validate([
            'token'        => 'required|min:10',
            'new_password' => 'required|min:6',
        ]);

        $data = RequestValidator::sanitize($data);

        $isReset = $this->authService->resetPassword($data['token'], $data['new_password']);

        if (!$isReset)Response::error(500, "An error has occurred");

        Response::success([], "Password has been reset successfully.");
    }
}