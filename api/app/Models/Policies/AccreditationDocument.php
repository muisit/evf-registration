<?php

namespace App\Models\Policies;

use App\Models\AccreditationDocument as Model;
use App\Support\Contracts\EVFUser;

class AccreditationDocument
{
    private function isCheckin(EVFUser $user, ?Model $model = null)
    {
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;

        if (!empty($model) && !empty($eventId)) {
            if ($eventId != $model->accreditation->event_id) {
                return false;
            }
        }

        if (!empty($eventId) && $user->hasRole(['checkin:' . $eventId])) {
            return true;
        }
        return false;
    }

    private function isCheckout(EVFUser $user, ?Model $model = null)
    {
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;

        if (!empty($model) && !empty($eventId)) {
            if ($eventId != $model->accreditation->event_id) {
                return false;
            }
        }

        if (!empty($eventId) && $user->hasRole(['checkout:' . $eventId])) {
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
        if ($this->isCheckin($user) || $this->isCheckout($user)) {
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
        if ($this->isCheckin($user) || $this->isCheckout($user)) {
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
        if ($this->isCheckin($user)) {
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
        if ($this->isCheckin($user) || $this->isCheckout($user)) {
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
        if ($this->isCheckin($user) || $this->isCheckout($user)) {
            return true;
        }

        return false;
    }
}
