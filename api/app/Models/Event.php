<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Support\Services\OverviewService;
use App\Support\Services\AccreditationOverviewService;

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

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'competition_event', 'event_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'event_id', 'event_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(AccreditationTemplate::class, 'event_id', 'event_id');
    }

    public function overview(): array
    {
        return (new OverviewService($this))->create();
    }

    public function accreditationOverview(): array
    {
        return (new AccreditationOverviewService($this))->create();
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

    public function allowGenerationOfAccreditations()
    {
        if (!empty($this->event_config)) {
            $config = json_decode($this->event_config);
            if ($config !== false && isset($config->no_accreditations)) {
                // inverse check, because the configuration indicates we are NOT using accreditations
                return !($config->no_accreditations == true);
            }
        }
        // by default, allow generation of accreditations
        return true;
    }

    public function useRegistrationApplication()
    {
        if (!empty($this->event_config)) {
            \Log::debug("config is not empty " . json_encode($this->event_config));
            $config = json_decode($this->event_config);
            \Log::debug("result is " . json_encode($config));
            if ($config !== false && isset($config->use_registration)) {
                \Log::debug("value set, testing for truthness " . ($config->use_registration == true ? 'truth' : 'false'));
                return $config->use_registration == true;
            }
        }
        \Log::debug("config is empty for " . $this->getKey() . ':' . json_encode($this->config));
        // by default, do not use the registration application for events
        return false;
    }
}
