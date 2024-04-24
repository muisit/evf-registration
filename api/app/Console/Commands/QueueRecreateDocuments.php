<?php
 
namespace App\Console\Commands;
 
use App\Models\Event;
use App\Jobs\RecreateSummary;
use Illuminate\Console\Command;
 
class QueueRecreateDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evf:recreatedocs {event : the ID of the event} {type : the document type} {id : the document type model id}';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-schedule CreateDocuments jobs';
 
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $eventId = $this->argument('event');
        $modelId = $this->argument('id');
        $type = $this->argument('type');
        dispatch(new RecreateSummary(Event::find(intval($eventId)), $type, intval($modelId)));
    }
}
