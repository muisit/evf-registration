<?php

namespace App\Http\Controllers\Device;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Base extends Controller
{
    public function only($fields)
    {
        $obj = json_decode(request()->getContent(), true);
        $keys = array_keys($obj);
        $availableKeys = array_intersect($keys, $fields);
        $retval = (object)[];
        foreach ($availableKeys as $field) {
            $retval->{$field} = $obj[$field];
        }
        return $retval;
    }
}
