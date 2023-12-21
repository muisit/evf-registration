<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function image($name, $ext)
    {
        $fname = "none.dat";
        if ($ext === null) $ext = "jpg";
        $fname = "img_" . $this->getKey() . "_" . $name . "." . $ext;

        $filename = storage_path('app/templates/' . $fname);
        return $filename;
    }


    /*
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'TD_Role_Template', 'template_id', 'role_id');
    }*/
}
