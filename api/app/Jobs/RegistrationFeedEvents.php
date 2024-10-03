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
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class RegistrationFeedEvents extends Job implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Fencer $fencer;
    public Competition $competition;
    public bool $isCancelled;
    /**
     * Create a new job instance.
     */
    public function __construct(Fencer $fencer, Competition $competition, bool $isCancelled)
    {
        $this->fencer = $fencer;
        $this->competition = $competition;
        $this->isCancelled = $isCancelled;
    }

    public function uniqueId(): string
    {
        return ($this->fencer->getKey() ?? '') . '_' . ($this->competition->getKey() ?? '') . '_' . ($this->isCancelled ? 'true' : 'false');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->fencer->triggersEvent('register')) {
            RegisterForEvent::dispatch($this->fencer, $this->competition, $this->isCancelled);
        }
    }
}
