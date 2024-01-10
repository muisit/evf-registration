<?php

namespace App\Models;

use App\Support\Contracts\AccreditationRelation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kirschbaum\PowerJoins\PowerJoins;
use Illuminate\Database\Query\Builder;

class Result extends Model
{
    use PowerJoins;

    protected $table = 'TD_Result';
    protected $primaryKey = 'result_id';
    public $timestamps = false;
    protected $guarded = [];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'result_competition', 'competition_id');
    }

    public function fencer(): BelongsTo
    {
        return $this->belongsTo(Fencer::class, 'result_fencer', 'fencer_id');
    }
}
