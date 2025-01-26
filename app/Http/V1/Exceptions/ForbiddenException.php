<?php

namespace App\Http\V1\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends Exception
{
    protected $statusCode = Response::HTTP_FORBIDDEN;

    protected $code = 10005;

    protected $type = 'forbidden';

    protected $message = 'Forbidden.';
}
