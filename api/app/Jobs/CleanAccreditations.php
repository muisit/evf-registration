<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\AccreditationUser;
use App\Models\Accreditation;
use App\Support\Services\PDFService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class CleanAccreditations extends Job implements ShouldBeUniqueUntilProcessing
{
    public $event = null;
    public $forced = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Event $event, $doForced = false)
    {
        $this->event = $event->withoutRelations();
        $this->forced = $doForced;
    }

    public function uniqueId(): string
    {
        return strval($this->event->getKey());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (
                $this->forced
            ||  (  $this->event->exists
                && ($this->event->isFinished() || !$this->event->allowGenerationOfAccreditations())
                )
        ) {
            $this->cleanEventPath();
            AccreditationUser::whereIn('accreditation_id', Accreditation::where('event_id', $this->event->getKey())->get()->pluck('id'))->delete();
            Accreditation::where('event_id', $this->event->getKey())->delete();
        }
    }

    private function cleanEventPath()
    {
        $basePath = PDFService::pdfPath($this->event);
        \Log::debug("cleaning event path $basePath");
        if (file_exists($basePath) && is_dir($basePath)) {
            // see https://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
            $it = new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                \Log::debug("removing " . $file->getRealPath());
                if ($file->isDir()) {
                    @rmdir($file->getRealPath());
                } else {
                    @unlink($file->getRealPath());
                }
            }
            @rmdir($path);
        }
    }
}
