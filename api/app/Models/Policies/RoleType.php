<?php

namespace App\Models\Policies;

use App\Models\RoleType as Model;
use App\Support\Contracts\EVFUser;

class RoleType
{
    // RoleType can only ever be editted by sysop
    public function before(EVFUser $user, string $ability): bool | null
    {
        if ($user->hasRole("sysop")) return true;
        return null;
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
