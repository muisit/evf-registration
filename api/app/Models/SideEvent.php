<?php

namespace App\Models;

use App\Support\Contracts\AccreditationRelation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kirschbaum\PowerJoins\PowerJoins;
use Carbon\Carbon;

class SideEvent extends Model implements AccreditationRelation
{
    use PowerJoins;

    protected $table = 'TD_Event_Side';
    //protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function competition(): HasOne
    {
        return $this->hasOne(Competition::class, 'competition_id', 'competition_id');
    }

    public function hasStarted()
    {
        if (empty($this->starts)) return false;

        $now = Carbon::now();
        $dateStart = new Carbon($this->starts);
        return $now->greaterThanOrEqualTo($dateStart);
    }

    public function selectAccreditations(Event $event)
    {
        // select accreditations for all fencers that registered for this side event
        // only select actual participants (role=0, template for Athlete)
        $templateIdByType = AccreditationTemplate::byRoleId($event);
        $acceptableTemplates = $templateIdByType["r0"] ?? [];

        $registrations = Registration::where('registration_event', $this->getKey())
            ->whereColumn('registration_fencer', Accreditation::tableName() . '.fencer_id');

        $accreditations = Accreditation::with(['fencer', 'template'])
            ->whereIn('template_id', $acceptableTemplates)
            ->where('event_id', $event->getKey())
            ->whereExists($registrations)
            ->get();
        return $accreditations;
    }

}
