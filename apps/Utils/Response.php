<?php

namespace App\Utils;

class Response
{
    /**
     * Send a JSON response.
     *
     * @param int $status HTTP status code
     * @param mixed $data Response data
     * @param string|null $message Optional message
     */
    public static function sendJson($status, $data = null, $message = null, $success)
    {
        // Set the HTTP status code
        http_response_code($status);

        // Set content type to JSON
        header('Content-Type: application/json');

        // Prepare the response structure
        $response = [
            'success' => $success,
            'status' => $status,
            'message' => $message ?? self::getStatusMessage($status),
            'data' => $data,
        ];

        // Encode and send the response
        echo json_encode($response);
        exit();
    }

    /**
     * Send a success response.
     *
     * @param mixed $data Response data
     * @param string|null $message Optional message
     */
    public static function success($data = null, $message = null)
    {
        self::sendJson(200, $data, $message ?? 'Success',  true);
    }

    /**
     * Send an error response.
     *
     * @param int $status HTTP status code
     * @param string|null $message Optional error message
     * @param mixed $data Additional error details (optional)
     */
    public static function error($status, $message = null, $data = null)
    {
        self::sendJson($status, $data, $message ?? 'An error occurred', false);
    }

    /**
     * Send a validation error response.
     *
     * @param array $errors Validation errors
     * @param string|null $message Optional message
     */
    public static function validationError($errors = [], $message = 'Validation failed')
    {
        self::sendJson(422, $errors, $message, false);
    }

    /**
     * Get the default HTTP status message for a given status code.
     *
     * @param int $status HTTP status code
     * @return string
     */
    private static function getStatusMessage($status)
    {
        $statusMessages = [
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
        ];

        return $statusMessages[$status] ?? 'Unknown Status';
    }
}
