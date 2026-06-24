<?php
namespace App\Utils;
use App\Utils\Response;
use App\Utils\Utility;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ValidationFailedException;


class ExceptionHandler
{
    public static function handle($e)
    {
        // log everything
        Utility::log($e->getMessage(), 'error', 'ExceptionHandler', [], $e);
        
        if ($e instanceof \InvalidArgumentException) {
            Response::error(400, $e->getMessage());
            return;
        }

        if ($e instanceof ResourceAlreadyExistsException) {
            Response::error(409, $e->getMessage());
            return;
        }

        if ($e instanceof ResourceNotFoundException) {
            Response::error(404, $e->getMessage());
            return;
        }

        if ($e instanceof ValidationFailedException) {
            Response::error(422, $e->getMessage());
            return;
        }

        Response::error(500, "Internal Server Error");
    }
}

