<?php
 
namespace App\Console\Commands;
 
use App\Jobs\CheckDirtyBadges;
use Illuminate\Console\Command;
 
class QueueCheckDirtyBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evf:dirty';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule the CheckDirtyBadges job';
 
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        dispatch(new CheckDirtyBadges());
    }
}
