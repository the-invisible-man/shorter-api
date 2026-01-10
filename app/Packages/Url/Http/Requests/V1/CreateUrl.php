<?php

namespace App\Packages\Url\Http\Requests\V1;

use App\Http\V1\Requests\Request;

class CreateUrl extends Request
{
    public function rules(): array
    {
        return [
            'long_url' => 'required|url',
            'turnstile_token' => 'required|string',
        ];
    }

    public function transform(): array
    {
        return [
            'long_url' => $this->get('long_url'),
            'turnstile_token' => $this->get('turnstile_token'),
        ];
    }
}
