<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Device extends Model
{
    protected $table = 'device_ids';
    protected $guarded = [];

    protected $casts = [
        'platform' => 'array'
    ];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(DeviceUser::class, 'device_user_id', 'id');
    }
}
