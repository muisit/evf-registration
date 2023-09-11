<?php

namespace App\Support\Services;

use App\Models\Country;
use App\Models\Fencer;

class AutocompleteService
{
    public function search(string $name, ?Country $country)
    {
        $validatedName = str_replace('%', '%%', validate_string($name));
        $query = Fencer::where('fencer_surname', 'LIKE', $validatedName . '%');

        if (!empty($country)) {
            $query->where('fencer_country', $country->getKey());
        }
        $query->orderBy('fencer_surname', 'asc')->orderBy('fencer_firstname', 'asc')->orderBy('fencer_id', 'asc');

        return $query->get();
    }
}
