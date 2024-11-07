<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use App\Support\Contracts\EVFUser as EVFUserContract;
use App\Support\Traits\EVFUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTimeImmutable;

class DeviceUser extends Model implements AuthenticatableContract, AuthorizableContract, EVFUserContract
{
    use Authorizable;
    use Authenticatable;
    use EVFUser;

    protected $table = 'device_users';
    protected $fillable = [];
    protected $guarded = [];
    protected $casts = [
        'preferences' => 'array'
    ];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
            $model->created_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            // create default preferences to apply to followers and following
            $model->preferences = [
                'account' => [
                    'followers' => ['handout', 'ranking', 'result', 'register'],
                    'following' => ['handout', 'ranking', 'result', 'register']
                ]
            ];
        });

        static::deleting(function ($model) {
            Follow::where('device_user_id', $model->getKey())->delete();
            Device::where('device_user_id', $model->getKey())->delete();
            $model->feeds()->sync([]);
        });

        static::saving(function ($model) {
            $model->updated_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        });
    }

    // see if this eventType should trigger an actual event
    public function triggersEvent($eventType)
    {
        $prefs = $this->preferences['account']['followers'];
        return is_array($prefs) && in_array($eventType, $prefs);
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
        return $this->email ?? '';
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

    public function feeds(): BelongsToMany
    {
        return $this->belongsToMany(DeviceFeed::class, 'device_user_feeds', 'device_user_id', 'device_feed_id');
    }

    public function following(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function mergeWith(DeviceUser $user)
    {
        // Get all fencers that we follow from this old user, convert them to this user
        // Take care not to create duplicates
        $fids = Follow::where('device_user_id', $this->getKey())->select('fencer_id')->get()->pluck('fencer_id');
        foreach (Follow::where('device_user_id', $user->getKey())->get() as $follower) {
            if (!$fids->contains($follower->fencer_id) && $follower->fencer_id !== $this->fencer_id) {
                $follower->device_user_id = $this->getKey();
                $follower->save();
            }
            else {
                $follower->delete(); // remove the duplicate
            }
        }

        // Update all devices of the old user to link to the new user
        Device::where('device_user_id', $user->getKey())->update(['device_user_id' => $this->getKey()]);

        // Get all the feed IDs of the old user and the new user and merge the ids. Then sync the
        // new user to this list of ids.
        // This may duplicate feed items that are created in different languages, but that would be
        // rare hopefully
        $oldids = $user->feeds()->get()->pluck('id');
        $newids = $this->feeds()->get()->pluck('id')->concat($oldids)->unique();
        $this->feeds()->sync($newids);
    }
}
