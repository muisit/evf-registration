<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class SideEvent extends Model
{
    protected $table = 'TD_Event_Side';
    //protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function competition(): HasOne
    {
        return $this->hasOne(Competition::class, 'competition_id', 'competition_id');
    }

    public function hasStarted()
    {
        if (empty($this->starts)) return false;

        $now = Carbon::now();
        $dateStart = new Carbon($this->starts);
        return $now->greaterThanOrEqualTo($dateStart);
    }
}
