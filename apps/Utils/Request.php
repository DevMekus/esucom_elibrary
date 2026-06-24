<?php

namespace App\Utils;

class Request
{
    /**
     * Returns JSON or form body as associative array
     */
    public static function getBody()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // If JSON request
        if (strpos($contentType, 'application/json') !== false) {
            $raw = file_get_contents("php://input");
            return json_decode($raw, true);
        }

        // If form-urlencoded or multipart
        if (!empty($_POST)) {
            return $_POST;
        }

        // If GET request
        if (!empty($_GET)) {
            return $_GET;
        }

        return [];
    }
}
