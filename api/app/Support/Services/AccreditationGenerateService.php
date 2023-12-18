<?php

namespace App\Support\Services;

use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Category;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Role;
use App\Models\RoleType;
use App\Models\SideEvent;
use Illuminate\Support\Collection;
use DateTimeImmutable;

class AccreditationGenerateService
{
    public Accreditation $accreditation;

    public function __construct(Accreditation $accreditation)
    {
        $this->accreditation = $accreditation;
    }

    public function handle()
    {
        $path = $this->accreditation->path();
        if (file_exists($path)) {
            @unlink($path);
        }
        if (file_exists($path)) {
            throw new Exception("Unable to remove existing accreditation badge for " . $this->accreditation->path());
        }

        PDFService::generate($path, json_decode($this->accreditation->content));
        if (!file_exists($path)) {
            throw new Exception("Error generating new badge for " . $this->accreditation->path());
        }
        else {
            $this->accreditation->generated = date("Y-m-d H:i:s");
            $this->accreditation->file_hash = hash_file('sha256', $path, false);
            $this->accreditation->is_dirty = null;
            $this->accreditation->save();
        }
    }
}
