<?php

namespace App;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Ramsey\Uuid\Uuid;

abstract class Model extends BaseModel
{
    /**
     * Uses a uuid for the id field
     *
     * @var bool
     */
    public bool $shouldUseUuidId = true;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * No auto incrementing id column
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if ($this->shouldUseUuidId && ! $this->exists && $this->getKey() === null && is_null($this->getAttribute($this->getKeyName()))) {
            $this->setAttribute($this->getKeyName(), (string) Uuid::uuid4());
        }

        return parent::save();
    }
}
