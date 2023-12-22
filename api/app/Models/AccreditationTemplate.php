<?php

namespace App\Models;

use App\Support\Contracts\AccreditationRelation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationTemplate extends Model implements AccreditationRelation
{
    protected $table = 'TD_Accreditation_Template';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function accreditations(): HasMany
    {
        return $this->hasMany(Accreditation::class, 'template_id', 'id');
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

    public static function byRoleId(Event $event)
    {
        $templates = self::where('event_id', $event->getKey())->get();
        $templatesByRole = [];
        foreach ($templates as $template) {
            \Log::debug("parsing template " . $template->getKey());
            $roleIds = $template->forRoles();
            $roles = Role::whereIn('role_id', $roleIds)->get();
            foreach ($roles as $role) {
                $roleTypeKey = 'r' . $role->role_type;
                if (!isset($templatesByRole[$roleTypeKey])) {
                    $templatesByRole[$roleTypeKey] = [];
                }
                if (!in_array($template->getKey(), $templatesByRole[$roleTypeKey])) {
                    $templatesByRole[$roleTypeKey][] = $template->getKey();
                }
            }
            if (in_array(0, $roleIds)) {
                // the athlete role, for which there should/could only ever be one template
                $templatesByRole['r0'] = [$template->getKey()];
            }
        }
        return $templatesByRole;
    }

    // this is a convenience function to get the Role relation, until we can actually
    // use an intermediary table for this
    public static function parseForRole(Event $event, Role $role)
    {
        $templates = self::where('event_id', $event->getKey())->get();
        foreach ($templates as $template) {
            $roleIds = $template->forRoles();
            if (in_array($role->getKey(), $roleIds)) {
                return $template;
            }
        }
        return null;
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

    public function selectAccreditations(Event $event)
    {
        return Accreditation::with(['fencer', 'template'])->where('template_id', $this->getKey())->where('event_id', $event->getKey())->get();
    }
}
