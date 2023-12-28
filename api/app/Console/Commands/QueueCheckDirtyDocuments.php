<?php
 
namespace App\Console\Commands;
 
use App\Jobs\CheckSummaries;
use Illuminate\Console\Command;
 
class QueueCheckDirtyDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evf:documents';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule the CheckSummaries job';
 
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        dispatch(new CheckSummaries(true));
    }
}
