<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kirschbaum\PowerJoins\PowerJoins;
use DateTimeImmutable;

class AccreditationDocument extends Model
{
    use PowerJoins;

    public const STATUS_CREATED = 'C';
    public const STATUS_PROCESSING = 'P';
    public const STATUS_PROCESSED_GOOD = 'G';
    public const STATUS_PROCESSED_ERROR = 'E';
    public const STATUS_CHECKOUT = 'O';

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
            $this->created_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        }
        $this->updated_by = request()->user()?->getKey();
        $this->updated_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        parent::save($options);
    }
}
