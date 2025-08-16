<?php

namespace App\Models\Schemas\FE;

use App\Models\RoleType as Model;

class RoleType
{
    public $id;
    public $name;
    public $org_declaration;

    public function __construct(Model $data, $extensive = false)
    {
        $this->id = $data->getKey();
        $this->name = $data->role_type_name;
        $this->org_declaration = $data->org_declaration;
    }
}
