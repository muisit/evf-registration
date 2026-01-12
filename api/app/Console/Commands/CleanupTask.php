<?php

namespace App\Console\Commands;

use App\Support\Services\CleanupService;
use Illuminate\Console\Command;

class CleanupTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evf:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $service = new CleanupService();
        $service->handle();
    }
}
