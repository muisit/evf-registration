<?php

namespace App\Jobs;

use App\Support\Services\RankingStoreService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

// This job checks to see if all available accreditations for a fencer are still
// matching as far as data is concerned. It will remove accreditations that are
// no longer required, add new accreditations if required and create appropiate
// CreateBadge jobs for accreditations that have changed or have been added.
//
// When accreditations are removed, the accompanying files are removed as well
//
class CreateRanking extends Job implements ShouldBeUniqueUntilProcessing
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
        return "ranking";
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
