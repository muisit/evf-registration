<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationTemplate extends Model
{
    protected $table = 'TD_Accreditation_Template';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function accreditations(): HasMany
    {
        return $this->hasMany(Accreditation::class, 'id', 'template_id');
    }

    public function forRoles()
    {
        if (isset($this->content)) {
            $content = json_decode($this->content, true);
            if ($content !== false && isset($content["roles"]) && is_array($content['roles'])) {
                return $content["roles"];
            }
        }
        return [];
    }
}
