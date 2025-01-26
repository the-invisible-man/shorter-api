<?php

namespace App\Packages\Url\Events;

use App\Packages\Url\Models\Url;
use Illuminate\Foundation\Events\Dispatchable;

class UrlEvent
{
    use Dispatchable;

    public function __construct(public Url $url)
    {

    }
}
