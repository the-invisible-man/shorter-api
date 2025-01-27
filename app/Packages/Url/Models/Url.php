<?php

namespace App\Packages\Url\Models;

use App\Model;

/**
 * @property int $id
 * @property string $long_url
 * @property string $short_url
 */
class Url extends Model
{
    public bool $shouldUseUuidId = false;

    public $incrementing = true;

    public $keyType = 'int';

    /**
     * @return array
     */
    public function toCache(): array
    {
        return $this->only(['id', 'short_url', 'long_url']);
    }

    /**
     * @return string
     */
    public function toDestinationUrl(): string
    {
        return url($this->short_url);
    }
}
