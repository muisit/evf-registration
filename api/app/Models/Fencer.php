<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kirschbaum\PowerJoins\PowerJoins;

class Fencer extends Model
{
    use PowerJoins;

    public const PICTURE_NONE = 'N';
    public const PICTURE_UPLOADED = 'Y';
    public const PICTURE_ACCEPTED = 'A';
    public const PICTURE_REPLACEMENT = 'R';

    protected $table = 'TD_Fencer';
    protected $primaryKey = 'fencer_id';
    protected $guarded = [];
    public $timestamps = false;

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'fencer_country', 'country_id');
    }
}
