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

    /**
     * @param User $user
     * @param Model $model
     * 
     * @return bool
     */
    public function viewAny(EVFUser $user): bool | null
    {
        // someone can see a fencer if he/she is an organiser or a registrar
        // for any event. We can remove these roles to restrict the number
        // of people with broad fencer access
        $isOrganiser = $user->rolesLike('organiser:') + $user->rolesLike('registrar:');
        if (count($isOrganiser) > 0) {
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
        if ($user->hasRole(['hod:' . $model->fencer_country, 'superhod'])) {
            return true;
        }

        // all other people cannot see individual fencer data
        return false;
    }
}
