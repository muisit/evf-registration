<?php

namespace App\Models\Policies;

use App\Models\Event as Model;
use App\Support\Contracts\EVFUser;

class Event
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(EVFUser $user, string $ability): bool | null
    {
        if ($user->hasRole("sysop")) return true;
        return null;
    }

    /**
     * @param User $user
     * @param Model $model
     * 
     * @return bool
     */
    public function view(EVFUser $user, Model $model): bool | null
    {
        // people cannot view it after it has finished
        if ($model->isFinished()) return false;

        // organisation and HoD can view it if it has not finished
        if ($user->hasRole(['organisation:' . $model->getKey(), "hod"])) {
            return true;
        }

        // all other people cannot view it
        return false;
    }

    /**
     * @param User $user
     * @param Model $model
     * 
     * @return bool
     */
    public function viewRegistrations(EVFUser $user, Model $model): bool | null
    {
        // people cannot view it after the event has finished
        if ($model->isFinished()) return false;

        // organisation can view all registrations
        if ($user->hasRole('organisation:' . $model->getKey())) {
            return true;
        }

        // hods can view it if the registration period has started and the event is not finished
        if ($model->registrationHasStarted() && $user->hasRole("hod")) {
            return true;
        }

        // all other people cannot view it
        return false;
    }
}
