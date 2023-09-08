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
    public function create(): BasicData
    {
        $retval = new BasicData();

        $retval->add($this->getCategories());
        $retval->add($this->getWeapons());
        $retval->add($this->getCountries());
        $retval->add($this->getRoles());

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
        $rows = Role::joinRelationship('type')->orderBy('role_type_id', 'asc')->orderBy('role_name', 'asc')->get()->toArray();
        return Role::hydrate($rows);
    }
}

