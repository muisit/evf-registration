<?php

namespace App\Models\Schemas\FE;

use App\Models\WPUser as Model;

class WPUser
{
    public $id;
    public $name;
    public $email;
    public $login;

    public function __construct(Model $data, $extensive = false)
    {
        $this->id = $data->getKey();
        $this->email = $data->user_email;
        $this->name = $data->user_nicename;
        $this->login = $data->user_login;
    }
}
