<?php

namespace App\Packages\Url\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class UrlCreated extends UrlEvent implements ShouldDispatchAfterCommit
{
    // We extend ShouldDispatchAfterCommit to ensure that
    // this event isn't fired in the middle of a URL create
}
