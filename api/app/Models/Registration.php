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
}
