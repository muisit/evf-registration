<?php

namespace App\Models\Policies;

use App\Models\Accreditation as Model;
use App\Support\Contracts\EVFUser;

class Accreditation
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(EVFUser $user, string $ability): bool | null
    {
        if ($user->hasRole("sysop")) {
            \Log::debug("user is sysop");
            return true;
        }
        \Log::debug("policy accreditation::before returns null");
        return null;
    }

    private function isOrganiser(EVFUser $user)
    {
        \Log::debug("policy accreditation::isOrganiser");
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;

        // someone can see an accreditation if he/she is an organiser or handles accreditation
        // for a valid event. We can remove these roles to restrict the number
        // of people with broad accreditation access
        if (!empty($eventId) && $user->hasRole(['organiser:' . $eventId, 'accreditation:' . $eventId])) {
            return true;
        }
        return false;
    }

    /**
     * @param User $user
     * @param Model $model
     * 
     * @return bool
     */
    public function viewAny(EVFUser $user): bool | null
    {
        if ($this->isOrganiser($user)) {
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param Model $model
     * 
     * @return bool
     */
    public function view(EVFUser $user, Model $model): bool | null
    {
        if ($this->isOrganiser($user)) {
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
        if ($this->isOrganiser($user)) {
            return true;
        }

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
        \Log::debug("accreditation policy test update");
        if ($this->isOrganiser($user)) {
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param Model $model
     *
     * Handout can only be done by accreditation code users (ie: the accreditation desk)
     *
     * @return bool
     */
    public function handout(EVFUser $user, Model $model): bool
    {
        if ($this->isOrganiser($user) && $user->hasRole('code')) {
            \Log::debug("policy accreditation handout returns true");
            return true;
        }
        \Log::debug("policy accreditation handout returns false");

        return false;
    }

    /**
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function delete(EVFUser $user, Model $model): bool
    {
        if ($this->isOrganiser($user)) {
            return true;
        }

        return false;
    }
}
