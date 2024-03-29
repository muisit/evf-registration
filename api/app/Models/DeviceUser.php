<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use App\Support\Contracts\EVFUser as EVFUserContract;
use App\Support\Traits\EVFUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceUser extends Model implements AuthenticatableContract, AuthorizableContract, EVFUserContract
{
    use Authorizable;
    use Authenticatable;
    use EVFUser;

    protected $table = 'device_users';
    protected $fillable = [];
    protected $guarded = [];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
    
    public function getRememberTokenName()
    {
        return null;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getAuthName(): string
    {
        return $this->email;
    }

    public function getAuthSessionName(): string
    {
        $els = explode('\\', get_class($this));
        return strtolower(end($els));
    }

    public function getAuthRoles(?Event $event = null): array
    {
        $roles = ["device"];
        return $roles;
    }

    public function fencer(): BelongsTo
    {
        return $this->belongsTo(Fencer::class, 'fencer_id', 'fencer_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function feed(): HasMany
    {
        return $this->hasMany(DeviceFeed::class);
    }

    public function delete()
    {
        // delete all linked Devices and feed entries
        Device::where('device_user_id', $this->getKey())->delete();
        DeviceFeed::where('device_user_id', $this->getKey())->delete();
        return parent::delete();
    }
}
