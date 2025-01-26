<?php

namespace App\Http\V1\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class MissingCredentialsException extends Exception
{
    protected $statusCode = Response::HTTP_UNAUTHORIZED;

    protected $code = 10001;

    protected $type = 'missing_credentials';

    protected $message = 'Missing or malformed `Authorization` header.';
}
