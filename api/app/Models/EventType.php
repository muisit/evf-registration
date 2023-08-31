<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventType extends Model
{
    protected $table = 'TD_Event_Type';
    protected $primaryKey = 'event_type_id';
    public $timestamps = false;

    public const INDIVIDUAL = 1;
    public const WORLD = 2;
    public const TEAM = 3;
    public const CIRCUIT = 4;
}
