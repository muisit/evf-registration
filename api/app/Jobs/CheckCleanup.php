<?php

namespace App\Jobs;

use App\Models\Event;
use App\Support\Services\PDFService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class CheckCleanup extends Job implements ShouldBeUniqueUntilProcessing
{
    public function uniqueId(): string
    {
        return "globally_unique";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // find all events in the future
        $opens = date('Y-m-d', time() - 24 * 60 * 60);
        $events = Event::where('event_open', '>', $opens)->get();
        foreach ($events as $event) {
            if (!$event->allowGenerationOfAccreditations()) {
                dispatch(new CleanAccreditations($event));
            }
        }

        // then find all events in the past that still have files
        // We take all events that have closed at least a month
        $opens = date('Y-m-d', time() - 30 * 24 * 60 * 60);
        $events = Event::where('event_open', '<', $opens)->get();
        foreach ($events as $event) {
            $path = PDFService::PDFPath($event);
            if (file_exists($path) && is_dir($path)) {
                dispatch(new CleanAccreditations($event));
            }
        }
    }
}
