<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleType extends Model
{
    protected $table = 'TD_Role_Type';
    protected $primaryKey = 'role_type_id';
    public $timestamps = false;

    public const COUNTRY = 1;
    public const ORG = 2;
    public const EVF = 3;
    public const FIE = 4;
}
