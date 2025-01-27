<?php

namespace App\Packages\Url\Http\Serializers\V1;

use App\Http\V1\Serializers\Serializer;
use App\Model;
use App\Packages\Url\Models\Url;

class UrlSerializer extends Serializer
{

    /**
     * @param Model|Url $model
     * @return array
     */
    public function serialize(Model|Url $model): array
    {
        return [
            'path' => $model->short_url,
            'long_url' => $model->long_url,
            'short_url' => $model->toDestinationUrl(),
        ];
    }
}
