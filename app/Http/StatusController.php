<?php

namespace App\Http;

class StatusController
{
    public function ping(): string
    {
        return 'All systems are go. We are sure.';
    }
}
