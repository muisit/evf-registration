<?php

namespace App\Models\Policies;

use App\Models\DeviceUser;
use App\Models\Follow as Model;
use App\Support\Contracts\EVFUser;

class Follow
{
    /**
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function view(EVFUser $user, Model $model): bool | null
    {
        \Log::debug("testing Follow::view policy");
        // someone can 'see' a Follower if they are following, or if they are followed
        // this is only accessible to device-environments
        if ($user->hasRole("device") && ($user instanceof DeviceUser)) {
            if ($model->fencer_id == $user->fencer_id) {
                return true;
            }

            if ($model->device_user_id == $user->getKey()) {
                return true;
            }
        }
        // all other people cannot see follower related data
        return false;
    }

    /**
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function create(EVFUser $user): bool
    {
        \Log::debug("testing Follow::create policy");
        // someone can 'create' a Follower if they are a device user
        if ($user->hasRole("device") && ($user instanceof DeviceUser)) {
            return true;
        }
        // all other people cannot create followers
        return false;
    }

    /**
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function update(EVFUser $user, Model $model): bool
    {
        \Log::debug("testing Follow::update policy");
        // someone can 'update' a Follower if they are the owner
        // this is only accessible to device-environments
        if ($user->hasRole("device") && ($user instanceof DeviceUser)) {
            if ($model->device_user_id == $user->getKey()) {
                return true;
            }
        }
        // all other people cannot update followers
        return false;
    }
}
