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

    public function codes(): HasMany
    {
        return $this->hasMany(AccreditationUser::class, 'event_id', 'event_id');
    }

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'competition_event', 'event_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'event_id', 'event_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'registration_mainevent', 'event_id');
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
        $dateClose = (new Carbon($this->event_registration_close))->addDays(1);
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
            $config = json_decode($this->event_config);
            if ($config !== false && isset($config->use_registration)) {
                return $config->use_registration == true;
            }
        }
        // by default, do not use the registration application for events
        return false;
    }

    public function useAccreditationApplication()
    {
        if (!empty($this->event_config)) {
            $config = json_decode($this->event_config);
            if ($config !== false && isset($config->use_accreditation)) {
                return $config->use_accreditation == true;
            }
        }
        // by default, do not use the accreditation application for events
        return false;
    }

    public function generateFunctionalCodes()
    {
        AccreditationUser::where('event_id', $this->getKey())->where('accreditation_id', null)->delete();
        $user = new AccreditationUser();
        $user->code = $this->generateFunctionalCode(0);
        $user->type = "organiser";
        $user->event_id = $this->getKey();
        $user->save();

        $user = new AccreditationUser();
        $user->code = $this->generateFunctionalCode(1);
        $user->type = "accreditation";
        $user->event_id = $this->getKey();
        $user->save();

        $user = new AccreditationUser();
        $user->code = $this->generateFunctionalCode(2);
        $user->type = "checkin";
        $user->event_id = $this->getKey();
        $user->save();

        $user = new AccreditationUser();
        $user->code = $this->generateFunctionalCode(3);
        $user->type = "checkout";
        $user->event_id = $this->getKey();
        $user->save();

        $user = new AccreditationUser();
        $user->code = $this->generateFunctionalCode(4);
        $user->type = "dt";
        $user->event_id = $this->getKey();
        $user->save();
    }

    private function generateFunctionalCode($id)
    {
        $id1 = random_int(101, 999);
        $id2 = random_int(101, 999);
        $code = sprintf("%d%03d%03d", $id, $id1, $id2);
        $check = Accreditation::createControlDigit($code);
        return "99" . $code . $check . sprintf("%04d", $this->getKey());
    }

    public function checkCode($code)
    {
        if (!empty($this->event_config)) {
            $config = json_decode($this->event_config);
            if ($config === false) {
                $config = (object)[];
            }
            if (isset($config->codes)) {
                return in_array($code, $config->codes);
            }
        }
        return false;
    }
}
