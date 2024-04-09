<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use App\Support\Services\PDFService;

class Ranking extends Model
{
    protected $table = 'rankings';
    public $timestamps = true;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(RankingPosition::class);
    }

}
