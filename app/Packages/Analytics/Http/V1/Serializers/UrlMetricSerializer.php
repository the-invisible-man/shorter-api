<?php

namespace App\Packages\Analytics\Http\V1\Serializers;

use App\Http\V1\Serializers\Serializer;
use App\Model;
use App\Packages\Analytics\Models\UrlMetric;

class UrlMetricSerializer extends Serializer
{
    /**
     * @param Model|UrlMetric $model
     * @return array
     */
    public function serialize(Model|UrlMetric $model): array
    {
        return [
            'id' => $model->id,
            'path' => $model->path,
            'count' => $model->count,
        ];
    }
}
