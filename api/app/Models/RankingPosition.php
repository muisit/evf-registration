<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use App\Support\Services\PDFService;

class RankingPosition extends Model
{
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
