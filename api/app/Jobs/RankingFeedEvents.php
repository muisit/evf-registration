<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Ranking;
use App\Events\RankingUpdate;

class RankingFeedEvents implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Ranking $ranking;

    /**
     * Create a new job instance.
     */
    public function __construct(Ranking $ranking)
    {
        $this->ranking = $ranking;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->ranking->positions as $position) {
            if ($position->fencer->triggersEvent('ranking')) {
                RankingUpdate::dispatch($position);
            }
        }
    }
}
