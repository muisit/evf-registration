<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\Access\Authorizable;
use App\Support\Contracts\EVFUser as EVFUserContract;
use App\Support\Traits\EVFUser;
use Illuminate\Support\Facades\DB;

class AccreditationUser extends Model implements AuthenticatableContract, AuthorizableContract, EVFUserContract
{
    use Authorizable;
    use Authenticatable;
    use EVFUser;

    protected $table = 'accreditation_codes';
    protected $fillable = [];
    protected $guarded = [];
    
    public function getRememberTokenName()
    {
        return null;
    }

    public function getAuthPassword()
    {
        return $this->code;
    }

    public function getAuthName(): string
    {
        return $this->accreditation?->fencer?->getFullName() ?? "General Code";
    }

    public function getAuthSessionName(): string
    {
        $els = explode('\\', get_class($this));
        return strtolower(end($els));
    }

    public function getAuthRoles(?Event $event = null): array
    {
        $roles = ["code", $this->type . ':' . $this->event_id, 'organisation:' . $this->event_id, "organisation", $this->type];
        return $roles;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class, 'accreditation_id', 'id');
    }

    public function delete()
    {
        // delete all linked AccreditationAudit entries
        AccreditationAudit::where('created_by', $this->getKey())->delete();
        return parent::delete();
    }
}
