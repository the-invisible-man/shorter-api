<?php

namespace App\Packages\Url;

use App\Packages\Url\Jobs\ProcessBulkUrlCreated;

class EventListener
{
    public function urlBulkCreated(ProcessBulkUrlCreated $event)
    {
        dispatch(new ProcessBulkUrlCreated($event->url));
    }
}
