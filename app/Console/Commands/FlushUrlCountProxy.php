<?php

namespace App\Console\Commands;

use App\Packages\Analytics\Commands\FlushUrlCount;

class FlushUrlCountProxy extends FlushUrlCount
{
    // This class is just here so the command can be
    // discovered by Laravel. A hacky solution to get
    // the job done. I prefer if the "packages" folder
    // continues to own all things related to its work
}
