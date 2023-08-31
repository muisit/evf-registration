<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Competition extends Model
{
    protected $table = 'TD_Competition';
    protected $primaryKey = 'competition_id';
    protected $guarded = [];
    public $timestamps = false;

    public function sideEvent(): HasOne
    {
        return $this->hasOne(SideEvent::class, 'competition_id', 'competition_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'competition_event', 'event_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'competition_category', 'category_id');
    }

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class, 'competition_weapon', 'weapon_id');
    }

    public function hasStarted()
    {
        $now = Carbon::now();
        $dateStart = new Carbon($this->competition_opens);
        return $now->greaterThanOrEqualTo($dateStart);
    }
}
