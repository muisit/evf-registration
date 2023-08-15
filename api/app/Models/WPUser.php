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

    public function getAuthRoles(Event $event): array
    {
        $retval = ["user"];
        $row = DB::table(env('WPDBPREFIX') . "usermeta")->where('user_id', $this->getKey())->where('meta_key', 'wp_capabilities')->first();
        if (is_object($row) && is_string($row->meta_value)) {
            $obj = unserialize($row->meta_value, ['allowed_classes' => false]);
            foreach ($obj as $key => $val) {
                if ($val && $key == 'administrator') {
                    $retval[] = "sysop";
                }
            }
        }

        $registrars = Registrar::where('user_id', $this->getKey())->get();
        foreach ($registrars as $entry) {
            if (!empty($entry->country_id)) {
                $retval[] = "hod:" . $entry->country_id;
            }
            else {
                $retval[] = "superhod";
            }
        }

        if (!empty($event)) {
            $roles = EventRole::where('user_id', $this->getKey())->where('event_id', $event->getKey())->get();
            foreach ($roles as $row) {
                $retval[] = $row->role_type . ':' . $eventid;
            }
        }
        return $retval;
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
