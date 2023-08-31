<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Support\Services\OverviewService;

class Event extends Model
{
    protected $table = 'TD_Event';
    protected $primaryKey = 'event_id';
    protected $guarded = [];
    public $timestamps = false;

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type', 'event_type_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'event_country', 'country_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(EventRole::class, 'event_id', 'event_id');
    }

    public function sides(): HasMany
    {
        return $this->hasMany(SideEvent::class, 'event_id', 'event_id');
    }

    public function overview(): array
    {
        return (new OverviewService($this))->create();
    }

    public function registrationHasStarted()
    {
        $dateOpen = new Carbon($this->event_registration_open);
        $now = Carbon::now();
        return $now->greaterThanOrEqualTo($dateOpen);
    }

    public function isOpenForRegistration()
    {
        $dateOpen = new Carbon($this->event_registration_open);
        $dateClose = new Carbon($this->event_registration_close);
        $now = Carbon::now();
        return $now->greaterThanOrEqualTo($dateOpen) && $now->lessThan($dateClose);
    }

    public function hasStarted()
    {
        $now = Carbon::now();
        $dateStart = new Carbon($this->event_open);
        return $now->greaterThanOrEqualTo($dateStart);
    }

    public function isFinished()
    {
        $now = Carbon::now();
        $dateEnd = (new Carbon($this->event_open))->addDays($this->event_duration);
        return $now->greaterThan($dateEnd);
    }
}
