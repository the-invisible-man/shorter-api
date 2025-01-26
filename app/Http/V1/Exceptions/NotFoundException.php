<?php

namespace App\Http\V1\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends Exception
{
    protected $statusCode = Response::HTTP_NOT_FOUND;

    protected $code = 10003;

    protected $type = 'not_found';

    protected $message = 'Resource not found.';
}
