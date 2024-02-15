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
                \Log::debug("auth fails because $eventId != " . $model->accreditation->event_id);
                return false;
            }
        }

        if (!empty($eventId) && $user->hasRole(['checkin:' . $eventId])) {
            \Log::debug("has checkIn");
            return true;
        }
        \Log::debug("auth fails because checkin role not found");
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
        \Log::debug("checking create policy");
        if ($this->isCheckin($user)) {
            \Log::debug("create allowed");
            return true;
        }

        \Log::debug("create not allowed");
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
        \Log::debug("checking update policy");
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
