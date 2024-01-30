<?php
 
namespace App\Console\Commands;
 
use App\Models\Event;
use App\Jobs\CleanAccreditations;
use Illuminate\Console\Command;
 
class QueueCleanAccreditations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evf:clean {event : the ID of the event}';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule the CleanAccreditations job for a specific event';
 
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $eventId = $this->argument('event');
        $event = Event::find($eventId);
        if (!empty($event)) {
            dispatch(new CleanAccreditations($event, true));
        }
    }
}
