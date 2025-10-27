<?php

namespace App\Models\Schemas\FE;

use App\Models\Country as Model;

class Country
{
    public $id;
    public $name;
    public $abbr;
    public $registered;
    public $flag;

    public function __construct(Model $data, $extensive = false)
    {
        $this->id = $data->getKey();
        $this->name = $data->country_name;
        $this->abbr = $data->country_abbr;
        $this->registered = $data->country_registered;
        $this->flag = $data->country_flag_path;
    }
}
