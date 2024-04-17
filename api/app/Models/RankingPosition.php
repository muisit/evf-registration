<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Kirschbaum\PowerJoins\PowerJoins;

class RankingPosition extends Model
{
    use PowerJoins;

    protected $table = 'ranking_positions';
    public $timestamps = false;

    protected $casts = [
        "settings" => "array",
    ];

    public function ranking(): BelongsTo
    {
        return $this->belongsTo(Ranking::class);
    }

    public function fencer(): BelongsTo
    {
        return $this->belongsTo(Fencer::class, 'fencer_id', 'fencer_id');
    }
}
