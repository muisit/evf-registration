<?php

namespace App\Models\Policies;

use App\Models\Weapon as Model;
use App\Support\Contracts\EVFUser;

class Weapon
{
    // Weapons are only ever available to sysop, and only in the viewAny ability
    public function before(EVFUser $user, string $ability): bool | null
    {
        if ($ability == 'viewAny' && $user->hasRole("sysop")) return true;
        return false;
    }

    public function viewAny(EVFUser $user): bool | null
    {
        return false;
    }

    public function view(EVFUser $user, Model $model): bool | null
    {
        return false;
    }

    public function create(EVFUser $user): bool | null
    {
        return false;
    }

    public function update(EVFUser $user): bool | null
    {
        return false;
    }

    public function delete(EVFUser $user): bool | null
    {
        return false;
    }

    public function forceDelete(EVFUser $user): bool | null
    {
        return false;
    }
}
