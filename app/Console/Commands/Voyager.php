<?php

namespace App\Console\Commands;

use App\Packages\Voyager\VoyagerService;
use Illuminate\Console\Command;

class Voyager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voyager:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts voyager service';

    /**
     * Execute the console command.
     */
    public function handle(VoyagerService $service): int
    {
        $this->info("Voyager 1.0-alpha (See logs for interactions)");
        $this->info("Started voyager service...");

        $service->run();

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
