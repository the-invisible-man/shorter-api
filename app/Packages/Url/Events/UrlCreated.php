<?php

namespace App\Packages\Url\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class UrlCreated extends UrlEvent implements ShouldDispatchAfterCommit
{
}
