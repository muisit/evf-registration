<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weapon extends Model
{
    protected $table = 'TD_Weapon';
    protected $primaryKey = 'weapon_id';
    public $timestamps = false;

    public const MF = 1;
    public const ME = 2;
    public const MS = 3;
    public const WF = 4;
    public const WE = 5;
    public const WS = 6;
}
