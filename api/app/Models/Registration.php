<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kirschbaum\PowerJoins\PowerJoins;

class Registration extends Model
{
    use PowerJoins;

    protected $table = 'TD_Registration';
    protected $primaryKey = 'registration_id';
    protected $guarded = [];
    public $timestamps = false;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'registration_mainevent', 'event_id');
    }

    public function sideEvent(): BelongsTo
    {
        return $this->belongsTo(SideEvent::class, 'registration_event', 'id');
    }

    public function fencer(): BelongsTo
    {
        return $this->belongsTo(Fencer::class, 'registration_fencer', 'fencer_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'registration_role', 'role_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'registration_country', 'country_id');
    }

    public function save(array $options = [])
    {
        if (empty($this->registration_role)) {
            $this->registration_role = 0;
        }

        // make sure only one registration for a fencer for a side-event or role exists
        // either role is 0, or side-event is null at the moment
        if (!$this->exists) {
            $query = Registration::where('registration_id', '<>', $this->getKey())
                ->where('registration_fencer', $this->registration_fencer)
                ->where('registration_role', $this->registration_role);
            if (empty($this->registration_event)) {
                $query->where('registration_event', null);
            }
            else {
                $query->where('registration_event', $this->registration_event);
            }
            $query->delete();
        }

        if (parent::save($options)) {
            Accreditation::makeDirty($this->fencer, $this->event);
        }
    }
}
