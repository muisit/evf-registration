<?php

namespace App\Support\Services;

use App\Models\Schemas\BasicData;
use App\Models\Category;
use App\Models\Country;
use App\Models\Event;
use App\Models\Weapon;
use App\Models\Role;
use App\Models\RoleType;
use Illuminate\Support\Facades\DB;

class BasicDataService
{
    public function create(string $restrict = ''): BasicData
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

    public static function getCutOff()
    {
        $result = DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->select('option_value')
            ->where('option_name', 'evfranking_ranking_count_included')
            ->first();
        $result = intval($result?->option_value);
        if ($result < 2) {
            $result = 5;
        }
        return $result;
    }

    public static function setCutOff($val)
    {
        $val = intval($val);
        if ($val < 2) {
            $val = 5;
        }
        DB::table(env('WPDBPREFIX', 'wp_') . 'options')->where('option_name', 'evfranking_ranking_count_included')->delete();
        DB::table(env('WPDBPREFIX', 'wp_') . 'options')->insert(['option_value' => $val, 'option_name' => 'evfranking_ranking_count_included']);
    }

    public static function getAPIUser()
    {
        $result = DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->select('option_value')
            ->where('option_name', 'evf_internal_user')
            ->first();
        return $result?->option_value ?? '';
    }

    public static function setAPIUser($val)
    {
        if (!empty(trim($val))) {
            DB::table(env('WPDBPREFIX', 'wp_') . 'options')->where('option_name', 'evf_internal_user')->delete();
            DB::table(env('WPDBPREFIX', 'wp_') . 'options')->insert(['option_value' => $val, 'option_name' => 'evf_internal_user']);
        }
    }

    public static function getAPIKey()
    {
        $result = DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->select('option_value')
            ->where('option_name', 'evf_internal_key')
            ->first();
        return $result?->option_value ?? '';
    }

    public static function setAPIKey($val)
    {
        if (!empty(trim($val))) {
            DB::table(env('WPDBPREFIX', 'wp_') . 'options')->where('option_name', 'evf_internal_key')->delete();
            DB::table(env('WPDBPREFIX', 'wp_') . 'options')->insert(['option_value' => $val, 'option_name' => 'evf_internal_key']);
        }
    }

    public static function rankedEvents()
    {
        $first = Event::where('event_in_ranking', 'Y')->orderBy('event_open', 'asc')->select('event_open')->first();
        return Event::where('event_open', '>=', $first?->event_open ?? '2020-01-01')->orderBy('event_open', 'asc')
            // sides and codes explicitely execute a query in the Event schema, so eager loading is useless
            ->with('competitions')->with('templates')->with('type')
            ->get();
    }
}
