<?php

namespace App\Models\Schemas\FE;

use App\Models\Registrar as Model;

class Registrar
{
    public $id;
    public $user;
    public $country;
    public $country_name;
    public $name;

    public function __construct(Model $data, $extensive = false)
    {
        $this->id = $data->getKey();
        $this->user = $data->user_id;
        $this->name = $data->user_nicename ?? $data->user?->user_nicename;
        $this->country = $data->country_id;
        $this->country_name = $data->country_name ?? ($data->country?->country_name ?? 'General Administration');
    }
}
