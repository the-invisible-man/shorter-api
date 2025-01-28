<?php

namespace App\Packages\Url\Http\Requests\V1;

use App\Http\V1\Requests\Request;

class CreateJob extends Request
{
    public function rules(): array
    {
        return [
            'file' => 'required|mimetypes:text/csv,text/plain,application/csv|max:2048',
        ];
    }
}
