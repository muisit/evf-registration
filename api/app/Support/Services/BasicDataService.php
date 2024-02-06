<?php

namespace App\Support\Services;

use App\Models\Schemas\BasicData;
use App\Models\Category;
use App\Models\Country;
use App\Models\Weapon;
use App\Models\Role;
use App\Models\RoleType;
use DB;

class BasicDataService
{
    public function create(string $restrict): BasicData
    {
        $retval = new BasicData();

        if ($restrict == '' || $restrict == 'categories') $retval->add($this->getCategories());
        if ($restrict == '' || $restrict == 'weapons') $retval->add($this->getWeapons());
        if ($restrict == '' || $restrict == 'countries') $retval->add($this->getCountries());
        if ($restrict == '' || $restrict == 'roles') $retval->add($this->getRoles());

        return $retval;
    }

    private function getCategories()
    {
        return Category::orderBy('category_value', 'asc')->get();
    }

    private function getWeapons()
    {
        return Weapon::orderBy('weapon_abbr', 'asc')->get();
    }

    private function getCountries()
    {
        return Country::orderBy('country_name', 'asc')->get();
    }

    private function getRoles()
    {
        return Role::with('type')->orderBy('role_type', 'asc')->orderBy('role_name', 'asc')->get();
    }
}
