<?php

namespace App\Packages\Url\Models;

use App\Model;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $long_url
 * @property string $short_url
 */
class Url extends Model
{
    public function toCache(): array
    {
        return $this->only(['id', 'short_url', 'long_url']);
    }
}
