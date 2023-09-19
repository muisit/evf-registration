<?php

namespace App\Models\Policies;

use App\Models\Fencer as Model;
use App\Support\Contracts\EVFUser;

class Fencer
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(EVFUser $user, string $ability): bool | null
    {
        if ($user->hasRole("sysop")) return true;
        return null;
    }

    private function isOrganiserOrRegistrar(EVFUser $user)
    {
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;

        // someone can see a fencer if he/she is an organiser or a registrar
        // for a valid event. We can remove these roles to restrict the number
        // of people with broad fencer access
        if (!empty($eventId) && $user->hasRole(['organiser:' . $eventId, 'registrar:' . $eventId])) {
            return true;
        }
        return false;
    }

    private function isHodForCountry(EVFUser $user, int $testForId = null)
    {
        // super-heads-of-delegation are always HoD
        if ($user->hasRole('superhod')) {
            return true;
        }

        $countryObject = request()->get('countryObject');
        $countryId = !empty($countryObject) ? $countryObject->getKey() : null;
        if (!empty($countryId)) {
            if ($user->hasRole(['hod:' . $countryId, 'superhod'])) {
                return $testForId == null || $countryId == $testForId;
            }
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
        if ($this->isOrganiserOrRegistrar($user)) {
            return true;
        }

        // all other people cannot see all fencer data
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
        // anyone that can see all data can see this data
        if ($this->viewAny($user)) {
            return true;
        }

        // someone can see a fencer if he/she is the HoD of the country
        // of that fencer, or a super-HoD
        if ($this->isHodForCountry($user, $model->fencer_country)) {
            return true;
        }

        // all other people cannot see individual fencer data
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function create(EVFUser $user): bool
    {
        if ($this->isOrganiserOrRegistrar($user)) {
            return true;
        }

        // heads-of-delegation can create fencers
        if ($this->isHodForCountry($user)) {
            return true;
        }

        // all other people cannot create fencer data
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
        // organisers with registration rights can update a fencer model
        if ($this->isOrganiserOrRegistrar($user)) {
            return true;
        }

        // a regular HoD can only update fencers of their own country
        if ($this->isHodForCountry($user, $model->fencer_country)) {
            return true;
        }

        // all other people cannot create fencer data
        return false;
    }

    /**
     * @param User $user
     * 
     * @return bool
     */
    public function pictureState(EVFUser $user): bool
    {
        // organisers with registration rights can update the picture state
        if ($this->isOrganiserOrRegistrar($user)) {
            return true;
        }

        // all other people cannot change the state of pictures
        return false;
    }
}
