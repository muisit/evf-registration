<?php

namespace App\Models;

use App\Models\Schemas\BlockStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DeviceFeed extends Model
{
    public const NOTIFICATION = 1;
    public const NEWS = 2;
    public const MESSAGE = 3;
    public const RESULT = 4;
    public const RANKING = 5;

    protected $table = 'device_feeds';
    protected $guarded = [];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(DeviceUser::class);
    }
}
