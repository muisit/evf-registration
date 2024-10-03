<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Ranking;
use App\Events\RankingUpdate;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class RankingFeedEvents extends Job implements ShouldQueue, ShouldBeUniqueUntilProcessing
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

    public function uniqueId(): string
    {
        return $this->ranking->getKey() ?? '';
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
