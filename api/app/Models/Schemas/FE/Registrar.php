<?php

namespace App\Models\Schemas\FE;

use App\Models\Registrar as Model;

class Registrar
{
    public $id;
    public $user_id;
    public $country_id;
    public $country_name;
    public $user_name;

    public function __construct(Model $data, $extensive = false)
    {
        $this->id = $data->getKey();
        $this->user_id = $data->user_id;
        $this->user_name = $data->user_nicename ?? $data->user?->user_nicename;
        $this->country_id = $data->country_id;
        $this->country_name = $data->country_name ?? ($data->country?->country_name ?? 'General Administration');
    }
}
