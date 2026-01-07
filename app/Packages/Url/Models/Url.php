<?php

namespace App\Packages\Url\Models;

use App\Model;

/**
 * @property int        $id
 * @property string     $long_url
 * @property string     $short_url
 * @property boolean    $flagged
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
        return $this->only(['id', 'short_url', 'long_url', 'flagged']);
    }

    /**
     * @return string
     */
    public function toDestinationUrl(): string
    {
        return url('r/'.$this->short_url);
    }

    /**
     * @return bool
     */
    public function isFlagged(): bool
    {
        return $this->flagged;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'flagged' => 'boolean',
        ];
    }
}
