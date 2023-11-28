<?php

namespace App\Models\Policies;

use App\Models\Registration as Model;
use App\Support\Contracts\EVFUser;

class Registration
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

    private function isOrganiser(EVFUser $user)
    {
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;

        // someone can see a registration if he/she is an organiser, a registrar, a cashier
        // or an accreditor for the event of the registration, which should be the same as the global event
        if (!empty($eventId) && $user->hasRole(['organisation:' . $eventId])) {
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

    public function hod(EVFUser $user)
    {
        return $this->isHodForCountry($user);
    }

    // can perform registration functions for the current event
    public function register(EVFUser $user)
    {
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;
        if (!empty($eventId) && $user->hasRole(['organiser:' . $eventId, 'registrar:' . $eventId])) {
            return true;
        }
        return false;
    }

    // can perform cashier functions for the current event
    public function cashier(EVFUser $user)
    {
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;
        if (!empty($eventId) && $user->hasRole(['organiser:' . $eventId, 'cashier:' . $eventId])) {
            return true;
        }
        return false;
    }

    // can perform accreditation functions for the current event
    public function accredit(EVFUser $user)
    {
        // see if we have a global request object for the event
        $event = request()->get('eventObject');
        $eventId = (!empty($event) && $event->exists) ? $event->getKey() : null;
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

        // someone can see all registrations (of a country) if he/she is the HoD of the country
        // of the fencer of that registration, or a super-HoD
        if ($this->isHodForCountry($user)) {
            return true;
        }

        // all other people cannot see all registration data
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

        // someone can see all registrations (of a country) if he/she is the HoD of the country
        // of the fencer of that registration, or a super-HoD
        if ($this->isHodForCountry($user, $model->registration_country)) {
            return true;
        }

        // all other people cannot see individual registration data
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function create(EVFUser $user): bool
    {
        // only organisers and registrars can create registrations, accreditors and cashier cannot
        if ($this->isOrganiserOrRegistrar($user)) {
            return true;
        }

        // heads-of-delegation can create registrations
        if ($this->isHodForCountry($user)) {
            return true;
        }

        // all other people cannot create registration data
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
        // organisers with registration rights can update a registration model
        if ($this->isOrganiser($user)) {
            return true;
        }

        // a regular HoD can only update registrations of their own country
        if ($this->isHodForCountry($user, $model->registration_country)) {
            return true;
        }

        // all other people cannot create registration data
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
        // only organisers and registrars can delete registrations, accreditors and cashier cannot
        if ($this->isOrganiserOrRegistrar($user)) {
            return true;
        }

        // HoDs can delete accreditations of their country only
        if ($this->isHodForCountry($user, $model->registration_country)) {
            return true;
        }
        return false;
    }
}
