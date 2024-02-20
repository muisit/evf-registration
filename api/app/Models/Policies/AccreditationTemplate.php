<?php

namespace App\Models\Policies;

use App\Models\AccreditationTemplate as Model;
use App\Support\Contracts\EVFUser;

class AccreditationTemplate
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(EVFUser $user, string $ability): bool | null
    {
        if ($user->hasRole("sysop")) return true;
        return null;
    }

    private function isOrganiser(EVFUser $user)
    {
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;

        // someone can see a template if he/she is an organiser for a valid event.
        // We can remove these roles to restrict the number of people with broad accreditation access
        if (!empty($eventId) && $user->hasRole(['organiser:' . $eventId])) {
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
        // only sysop can create
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
    public function delete(EVFUser $user, Model $model): bool
    {
        // only sysop can delete
        return false;
    }
}
