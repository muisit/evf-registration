<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FencerLabel extends Model
{
    protected $table = 'fencer_labels';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function fencer(): BelongsTo
    {
        return $this->belongsTo(Fencer::class, 'fencer_id', 'fencer_id');
    }
}
