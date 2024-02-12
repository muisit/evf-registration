<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AccreditationAudit extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array'
    ];

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(AccreditationUser::class, 'created_by');
    }

    public function save($options = [])
    {
        $this->created_at = Carbon::now()->toDateTimeString();
        $this->created_by = request()->user()?->getKey();
        parent::save($options);
    }

    public static function createFromAction($action, Accreditation $accreditation, array $payload)
    {
        $obj = new static();
        $obj->type = $action;
        $obj->accreditation_id = $accreditation->getKey();
        $obj->payload = $payload;
        $obj->save();
    }
}
