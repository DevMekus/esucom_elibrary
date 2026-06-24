<?php

namespace App\Middleware;

use App\Utils\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware
{
    private static function get_JWT_SECRET()
    {
        return $_ENV['JWT_SECRET'];
    }

    public static function generateToken($payload)
    {
        return JWT::encode($payload, self::get_JWT_SECRET(), 'HS256');
    }

    public static function verifyToken()
    {
        $headers = getallheaders();


        if (!isset($headers['Authorization'])) {
            Response::error(401, 'Authorization header is missing');
        }

        $authHeader = $headers['Authorization'];


        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error(400, 'Invalid Authorization header format');
        }

        $token = $matches[1];


        try {
            $decoded = JWT::decode($token, new Key(self::get_JWT_SECRET(), 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            Response::error(401, 'Invalid Authorization: Invalid or expired token');
        }
    }

    public static function decodeToken($jwt)
    {
        return JWT::decode($jwt, new Key(self::get_JWT_SECRET(), 'HS256'));
    }

    public function handle()
    {
        self::verifyToken(); // will exit with error if invalid
        return true;
    }
}
