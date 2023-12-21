<?php

namespace App\Models\Policies;

use App\Models\Registration as Model;
use App\Support\Contracts\EVFUser;

class WPUser
{
    public function sysop(EVFUser $user)
    {
        if ($user->hasRole("sysop")) return true;
        return false;
    }
}
