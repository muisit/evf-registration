<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kirschbaum\PowerJoins\PowerJoins;

class Role extends Model
{
    use PowerJoins;

    protected $table = 'TD_Role';
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    public const HOD = 2;
    public const COACH = 4;
    public const REFEREE = 7;
    public const VOLUNTEER = 11;
    public const DIRECTOR = 14;
    public const DT = 18;

    public function type(): BelongsTo
    {
        return $this->belongsTo(RoleType::class, 'role_type', 'role_type_id');
    }

    public function newInstance($attributes = [], $exists = false)
    {
        $model = parent::newInstance($attributes, $exists);
        if (isset($attributes['role_type_id'])) {
            $model->type = (new RoleType())->newInstance($attributes, $exists);
        }
        return $model;
    }
}
