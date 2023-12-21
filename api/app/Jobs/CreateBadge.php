<?php

namespace App\Jobs;

use App\Models\Accreditation;
use App\Support\Services\PDFGenerator;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

// This job checks to see if all available accreditations for a fencer are still
// matching as far as data is concerned. It will remove accreditations that are
// no longer required, add new accreditations if required and create appropiate
// CreateBadge jobs for accreditations that have changed or have been added.
//
// When accreditations are removed, the accompanying files are removed as well
//
class CreateBadge extends Job implements ShouldBeUniqueUntilProcessing
{
    public Accreditation $accreditation;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Accreditation $a)
    {
        $this->accreditation = $a->withoutRelations();
    }

    public function uniqueId(): string
    {
        return $this->accreditation->getKey();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // prevent any dirty-checks while we are processing
        $this->accreditation->is_dirty = null;
        $this->accreditation->save();

        $path = $this->accreditation->path();
        if (file_exists($path)) {
            @unlink($path);
        }
        if (!file_exists($path)) {
            $generator = app(PDFGenerator::class);
            $generator->generate($this->accreditation);
            $generator->save($path);

            if (!file_exists($path)) {
                $this->fail("Error creating accreditation, could not save file for " . $this->accreditation->getKey());
            }
            else {
                // theoretically it can be that the accreditation is set to is_dirty again while we were processing
                // in that case, it will automatically pop up after a while for reprocessing
                $this->accreditation->generated = date("Y-m-d H:i:s");
                $this->accreditation->file_hash = hash_file('sha256', $path, false);
                $this->accreditation->save();
            }
        }
        else {
            $this->fail('Could not remove existing accreditation for " . $this->accreditation->getKey()');
        }
    }
}
