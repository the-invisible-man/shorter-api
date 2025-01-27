<?php

namespace App\Packages\Url\Http\Serializers\V1;

use App\Http\V1\Serializers\Serializer;
use App\Model;
use App\Packages\Url\Models\BulkCsvJob;

class BulkCsvJobSerializer extends Serializer
{
    public function serialize(Model|BulkCsvJob $model): array
    {
        return [
            'id' => $model->id,
            'status' => $model->status,
        ];
    }
}
