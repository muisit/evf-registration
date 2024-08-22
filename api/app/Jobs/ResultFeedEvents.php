<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Competition;
use App\Events\ResultUpdate;

class ResultFeedEvents implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Competition $competition;
    /**
     * Create a new job instance.
     */
    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->competition->results as $result) {
            if ($result->fencer->triggersEvent('result')) {
                ResultUpdate::dispatch($position);
            }
        }
    }
}
