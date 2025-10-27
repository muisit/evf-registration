<?php

namespace App\Models\Schemas\FE;

use App\Models\Role as Model;

class Role
{
    public $id;
    public $name;
    public $type_id;
    public $type_name;
    public $org;

    public function __construct(Model $data, $extensive = false)
    {
        $this->id = $data->getKey();
        $this->name = $data->role_name;
        $this->type_id = $data->role_type;
        $this->type_name = $data->type->role_type_name;
        $this->org = $data->type->org_declaration;
    }
}
