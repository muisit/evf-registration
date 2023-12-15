<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registrar extends Model
{
    protected $table = 'TD_Registrar';
    protected $guarded = [];
    public $timestamps = false;

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }
}
