<?php

namespace App\Support\Services;

use App\Support\Contracts\EVFUser;
use App\Models\Country;

class DefaultCountryService
{
    public static function determineCountry(EVFUser $user): ?Country
    {
        $country = null;
        if ($user->hasRole("hod")) {
            $roles = $user->rolesLike("hod:");
            if (count($roles) > 0) {
                // only support 1 country, no double representations allowed
                $cid = intval(substr($roles[0], 4));
                $country = Country::where("country_id", $cid)->first();
            }
        }
        return $country;
    }
}
