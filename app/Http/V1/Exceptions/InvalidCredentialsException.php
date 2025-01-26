<?php

namespace App\Http\V1\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidCredentialsException extends Exception
{
    protected $statusCode = Response::HTTP_UNAUTHORIZED;

    protected $code = 10002;

    protected $type = 'invalid_credentials';

    protected $message = '`Authorization` header provided but invalid credentials (expired or incorrect).';
}
