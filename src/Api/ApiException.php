<?php

namespace A24\Api;

use Symfony\Component\HttpFoundation\Response;

class ApiException extends \Exception
{
    public function __construct($message, $code = Response::HTTP_BAD_REQUEST, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}