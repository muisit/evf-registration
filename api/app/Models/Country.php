<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Country extends Model
{
    protected $table = 'TD_Country';
    protected $primaryKey = 'country_id';
    public $timestamps = false;

    public const GBR = 1;
    public const ITA = 2;
    public const FRA = 11;
    public const GER = 12;
    public const NED = 21;
    public const TST = 46;
    public const OTH = 49;
}
