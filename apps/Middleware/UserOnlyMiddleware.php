<?php

namespace App\Middleware;

use App\Middleware\AuthMiddleware;
use App\Utils\Response;

class UserOnlyMiddleware
{
    public function handle()
    {
        $userData = AuthMiddleware::verifyToken();

        if (!isset($userData['role'])) {
            Response::error(403, 'Access denied: Authentication required');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['userid'] = $userData['userid'];

        return true;
    }
}
