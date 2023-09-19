<?php

namespace App\Models\Policies;

use App\Models\Country as Model;
use App\Support\Contracts\EVFUser;

class Country
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
        // someone can 'see' a country if he/she is the HoD of the country
        // or is a super-HoD
        if ($user->hasRole(['hod:' . $model->getKey(), 'superhod'])) {
            return true;
        }

        // organisers can switch between countries and see country-related data
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;
        \Log::debug('event ' . json_encode(request()->all()));
        if (!empty($eventId) && $user->hasRole(['organiser:' . $eventId, 'registrar:' . $eventId])) {
            return true;
        }

        // all other people cannot see country related data
        return false;
    }
}
