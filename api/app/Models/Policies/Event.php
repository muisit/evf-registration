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

    /**
     * @param User $user
     * @param Model $model
     * 
     * @return bool
     */
    public function viewAllRegistrations(EVFUser $user, Model $model): bool | null
    {
        // the organiser and the DT can view all registrations
        if ($this->organise($user, $model) || $this->dt($user, $model)) {
            return true;
        }
        // all other people cannot view all registrations
        return false;
    }

    // can perform organiser functions for this event
    public function organise(EVFUser $user, Model $model): bool | null
    {
        // see if we have a global request object for the event
        if ($user->hasRole(['organiser:' . $model->getKey()])) {
            return true;
        }
        return false;
    }

    // can perform accreditation functions for this event
    public function accredit(EVFUser $user, Model $model): bool | null
    {
        // see if we have a global request object for the event
        if ($user->hasRole(['organiser:' . $model->getKey(), 'accreditation:' . $model->getKey()])) {
            return true;
        }
        return false;
    }

    // can perform cashier functions for this event
    public function cashier(EVFUser $user, Model $model): bool | null
    {
        // see if we have a global request object for the event
        if ($user->hasRole(['organiser:' . $model->getKey(), 'cashier:' . $model->getKey()])) {
            return true;
        }
        return false;
    }

    // can perform register functions for this event
    public function register(EVFUser $user, Model $model): bool | null
    {
        // see if we have a global request object for the event
        if ($user->hasRole(['organiser:' . $model->getKey(), 'registrar:' . $model->getKey()])) {
            return true;
        }
        return false;
    }

    // can perform DT functions for this event
    public function dt(EVFUser $user, Model $model): bool | null
    {
        // see if we have a global request object for the event
        if ($user->hasRole(['organiser:' . $model->getKey(), 'dt:' . $model->getKey()])) {
            return true;
        }
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function create(EVFUser $user): bool
    {
        return false; // no one can create an event
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function configure(EVFUser $user, Model $model): bool
    {
        return $this->organise($user, $model);
    }

    /**
     * @param User $user
     * @param Model $model
     * 
     * @return bool
     */
    public function update(EVFUser $user, Model $model): bool
    {
        return false; // no-one can update an event except through before()
    }
}
