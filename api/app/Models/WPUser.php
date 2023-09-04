<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Lumen\Auth\Authorizable;
use App\Support\Contracts\EVFUser as EVFUserContract;
use App\Support\Traits\EVFUser;
use Illuminate\Support\Facades\DB;

class WPUser extends Model implements AuthenticatableContract, AuthorizableContract, EVFUserContract
{
    use Authenticatable;
    use Authorizable;
    use EVFUser;

    public $timestamps = false;

    public function getAuthPassword()
    {
        return $this->user_pass;
    }

    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthName(): string
    {
        return $this->display_name ?? '';
    }

    public function getAuthRoles(?Event $event = null): array
    {
        $capabilitiesValue = DB::table(env('WPDBPREFIX') . "options")->where('option_name', env('WPDBPREFIX') . 'user_roles')->first();
        $capabilities = !emptyResult($capabilitiesValue) && is_string($capabilitiesValue->option_value) ? unserialize($capabilitiesValue->option_value) : [];

        $retval = ["user"];
        $row = DB::table(env('WPDBPREFIX') . "usermeta")->where('user_id', $this->getKey())->where('meta_key', 'wp_capabilities')->first();
        if (is_object($row) && is_string($row->meta_value)) {
            $obj = unserialize($row->meta_value, ['allowed_classes' => false]);
            foreach ($obj as $key => $val) {
                if ($this->matchCapabilities($key, 'manage_ranking', $capabilities)) {
                    $retval[] = "sysop";
                }
                if ($this->matchCapabilities($key, 'manage_registration', $capabilities)) {
                    $retval[] = "sysop";
                }
            }
        }

        $registrars = Registrar::where('user_id', $this->getKey())->get();
        foreach ($registrars as $entry) {
            $retval[] = "hod";
            if (!empty($entry->country_id)) {
                $retval[] = "hod:" . $entry->country_id;
            }
            else {
                $retval[] = "superhod";
            }
        }

        if (!empty($event) && $event->exists) {
            $roles = $event->roles()->where('user_id', $this->getKey())->get();
        }
        else {
            $roles = EventRole::where('user_id', $this->getKey())->get();
        }
            
        if (!emptyResult($roles)) {
            foreach ($roles as $row) {
                $retval[] = 'organisation:' . $row->event_id;
                $retval[] = $row->role_type . ':' . $row->event_id;
            }
        }

        return array_unique($retval);
    }

    private function matchCapabilities($role, $capability, $options)
    {
        return isset($options[$role]) && isset($options[$role]['capabilities'][$capability]) && $options[$role]['capabilities'][$capability] === true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    protected $table = 'users';
    protected $primaryKey = 'ID';

    public function __construct(array $attributes = [])
    {
        $this->table = env('WPDBPREFIX', 'wp_') . $this->table;
        parent::__construct($attributes);
    }
}
