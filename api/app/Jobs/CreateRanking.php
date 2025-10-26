<?php

namespace App\Jobs;

use App\Support\Services\RankingStoreService;
use Illuminate\Contracts\Queue\ShouldBeUnique;

// This job recreates the most recent ranking based on the most recent events
class CreateRanking extends Job implements ShouldBeUnique
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function uniqueId(): string
    {
        return self::class;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new RankingStoreService();
        $service->handle();
    }
}
