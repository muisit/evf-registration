<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class RoleType extends Model
{
    protected $table = 'TD_Role_Type';
    protected $primaryKey = 'role_type_id';
    public $timestamps = false;

    public const COUNTRY = 1;
    public const ORG = 2;
    public const EVF = 3;
    public const FIE = 4;

    public static function rules()
    {
        return [
            'role_type_id' => ['required', 'int', 'min:0'],
            'role_type_name' => ['required','max:45','min:2'],
            'org_declaration' => ['required', Rule::in([self::COUNTRY, self::ORG, self::EVF, self::FIE])]
        ];
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'role_type', 'role_type_id');
    }
}
