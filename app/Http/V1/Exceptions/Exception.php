<?php

namespace App\Http\V1\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class Exception extends HttpException
{
    protected $statusCode;

    /**
     * The error type identifier.
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new API exception.
     *
     * @param null|mixed $message
     */
    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }

        parent::__construct($this->statusCode, $this->message, null, [], $this->code);
    }

    /**
     * Get the error type identifier.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Build and return the HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(Request $request)
    {
        return new JsonResponse($this->getData(), $this->getStatusCode());
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $data = [
            'error' => [
                'code' => $this->getCode(),
                'type' => $this->getType(),
                'message' => $this->getMessage(),
            ],
        ];

        return $data;
    }
}
