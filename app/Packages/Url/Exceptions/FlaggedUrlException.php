<?php

namespace App\Packages\Url\Exceptions;

use App\Packages\Url\Models\Url;

class FlaggedUrlException extends \Exception
{
    /**
     * @var Url
     */
    protected Url $url;

    /**
     * @param Url $url
     */
    public function __construct(Url $url)
    {
        parent::__construct('Flagged url');

        $this->url = $url;
    }
}
