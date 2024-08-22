<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Fencer;
use App\Models\Competition;
use App\Events\RegisterForEvent;

class RegistrationFeedEvents implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Fencer $fencer;
    public Competition $competition;
    public boolean $isCancelled;
    /**
     * Create a new job instance.
     */
    public function __construct(Fencer $fencer, Competition $competition, boolean $isCancelled)
    {
        $this->fencer = $fencer;
        $this->competition = $competition;
        $this->isCancelled = $isCancelled;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->fencer->triggersEvent('registration')) {
            RegisterForEvent::dispatch($this->fencer, $this->competition, $this->isCancelled);
        }
    }
}
