<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Accreditation;
use App\Support\Services\PDFService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class CleanAccreditations extends Job implements ShouldBeUniqueUntilProcessing
{
    public $event = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Event $event)
    {
        $this->event = $event->withoutRelations();
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
                $this->event->exists
            && ($this->event->isFinished() || !$this->event->allowGenerationOfAccreditations() )
        ) {
            $this->cleanEventPath();
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
