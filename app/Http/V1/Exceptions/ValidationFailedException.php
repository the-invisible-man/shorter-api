<?php

namespace App\Http\V1\Exceptions;

use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

class ValidationFailedException extends Exception
{
    public const API_ERROR_CODE = 10100;

    protected $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected $code = self::API_ERROR_CODE;

    protected $type = 'validation_failed';

    protected $message = 'Validation failed.';

    protected array $errors = [];

    /** @var \Illuminate\Validation\Validator|null */
    protected ?Validator $validator;

    public function __construct($validator = null, $message = null)
    {
        $this->validator = $validator;

        parent::__construct($message);
    }

    public function withErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function getData(): array
    {
        $data = parent::getData();

        $data['error']['errors'] = $this->getErrors();

        return $data;
    }

    protected function getErrors(): array
    {
        return $this->errors ?: $this->formatValidationErrors();
    }

    /**
     * Format the validation errors to be returned.
     *
     * @return array
     */
    protected function formatValidationErrors(): array
    {
        $messages = $this->validator->errors()->getMessages();
        $errors = [];
        foreach ($this->validator->failed() as $input => $rules) {
            $i = 0;
            foreach ($rules as $rule => $ruleInfo) {
                if (! isset($errors[$input])) {
                    $errors[$input] = [];
                }
                $errors[$input][] = [
                    'type' => strtolower($rule),
                    'message' => $messages[$input][$i],
                ];
                $i++;
            }
        }

        return $this->errors = $errors;
    }
}
