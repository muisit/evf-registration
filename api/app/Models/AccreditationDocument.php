<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationDocument extends Model
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AccreditationUser::class, 'created_by');
    }

    public function updator(): BelongsTo
    {
        return $this->belongsTo(AccreditationUser::class, 'updated_by');
    }

    public function save($options = [])
    {
        if (!$this->exists) {
            $this->created_by = request()->user()?->getKey();
        }
        $this->updated_by = request()->user()?->getKey();
        parent::save($options);
    }
}
