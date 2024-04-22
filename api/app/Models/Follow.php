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

    public function isBlocked(?bool $value = null) {
        if (!is_null($value)) {
            $this->setPreference('blocked', $value);
        }
        return isset($this->preferences['blocked']);
    }

    public function feedHandout(?bool $value = null) {
        if (!is_null($value)) {
            $this->setPreference('handout', $value);
        }
        return isset($this->preferences['handout']);
    }

    public function feedCheckin(?bool $value = null) {
        if (!is_null($value)) {
            $this->setPreference('checkin', $value);
        }
        return isset($this->preferences['checkin']);
    }

    public function feedCheckout(?bool $value = null) {
        if (!is_null($value)) {
            $this->setPreference('checkout', $value);
        }
        return isset($this->preferences['checkout']);
    }

    public function feedRanking(?bool $value = null) {
        if (!is_null($value)) {
            $this->setPreference('ranking', $value);
        }
        return isset($this->preferences['ranking']);
    }

    public function feedResult(?bool $value = null) {
        if (!is_null($value)) {
            $this->setPreference('result', $value);
        }
        return isset($this->preferences['result']);
    }

    public function feedRegister(?bool $value = null) {
        if (!is_null($value)) {
            $this->setPreference('register', $value);
        }
        return isset($this->preferences['register']);
    }
}
