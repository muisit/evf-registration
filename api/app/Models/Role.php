<?php

namespace App\Models;

use App\Support\Contracts\AccreditationRelation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kirschbaum\PowerJoins\PowerJoins;
use Illuminate\Database\Query\Builder;

class Role extends Model implements AccreditationRelation
{
    use PowerJoins;

    protected $table = 'TD_Role';
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    public const HOD = 2;
    public const COACH = 4;
    public const REFEREE = 7;
    public const MEDICAL = 9;
    public const VOLUNTEER = 11;
    public const DIRECTOR = 14;
    public const DT = 18;

    public function type(): BelongsTo
    {
        return $this->belongsTo(RoleType::class, 'role_type', 'role_type_id');
    }

    /*
    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(AccreditationTemplate::class, 'TD_Role_Template', 'role_id', 'template_id');
    }*/

    public function newInstance($attributes = [], $exists = false)
    {
        $model = parent::newInstance($attributes, $exists);
        if (isset($attributes['role_type_id'])) {
            $model->type = (new RoleType())->newInstance($attributes, $exists);
        }
        return $model;
    }

    public function selectAccreditations(Event $event)
    {
        // we do not select accreditations for the Athlete or Participant role... those collections are too large
        $template = AccreditationTemplate::parseForRole($event, $this);

        $registrations = Registration::where('registration_mainevent', $event->getKey())
            ->where('registration_role', $this->getKey())
            ->whereColumn('registration_fencer', Accreditation::tableName() . '.fencer_id');

        $accreditations = Accreditation::with(['fencer', 'template'])
            ->where('template_id', $template?->getKey())
            ->where('event_id', $event->getKey())
            ->whereExists($registrations)
            ->get();
        return $accreditations;
    }
}
