<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Follow extends Model
{
    protected $table = 'followers';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'preferences' => 'array'
    ];
 
    public static $allowedSettings = ["blocked", "unfollow", "handout", "checkin", "checkout", "ranking", "result", "register"];
    public static $allowedUserSettings = ["unfollow", "handout", "checkin", "checkout", "ranking", "result", "register"];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(DeviceUser::class, 'device_user_id', 'id');
    }

    public function fencer(): BelongsTo
    {
        return $this->belongsTo(Fencer::class, 'fencer_id', 'fencer_id');
    }

    public function setPreference($key, $value) {
        $data = $this->preferences;
        if ($value === true) {
            $data[$key] = true;
        }
        else if(isset($data[$key])) {
            unset($data[$key]);
        }
        $this->preferences = $data;
    }

    public function triggersOnEvent($eventType) {
        if (!$this->isBlocked() && is_array($this->preferences)) {
            \Log::debug("triggersOnEvent for $eventType testing on " . json_encode($this->preferences));
            return in_array($eventType, static::$allowedSettings)
                && isset($this->preferences[$eventType])
                && $this->preferences[$eventType] === true;
        }
        \Log::debug("triggersOnEvent for unmatched eventType $eventType");
        return false;
    }

    public function isBlocked(?bool $value = null) {
        if (!is_null($value)) {
            $this->setPreference('blocked', $value);
        }
        \Log::debug("returning blocked status for " . json_encode($this->preferences));
        return isset($this->preferences['blocked']) && $this->preferences['blocked'] === true;
    }
}
