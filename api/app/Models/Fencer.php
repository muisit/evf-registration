<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kirschbaum\PowerJoins\PowerJoins;

class Fencer extends Model
{
    use PowerJoins;

    public const PICTURE_NONE = 'N';
    public const PICTURE_UPLOADED = 'Y';
    public const PICTURE_ACCEPTED = 'A';
    public const PICTURE_REPLACEMENT = 'R';

    protected $table = 'TD_Fencer';
    protected $primaryKey = 'fencer_id';
    protected $guarded = [];
    public $timestamps = false;

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'fencer_country', 'country_id');
    }

    public function image()
    {
        if (empty($this->getKey()) || $this->getKey() < 1) {
            return resource_path('images/photoid.png');
        }
        $path = storage_path('app/fencers/fencer_' . $this->getKey() . '.dat');
        return $path;
    }

    public function accreditations(): HasMany
    {
        return $this->hasMany(Accreditation::class, 'fencer_id', 'fencer_id');
    }

    public function getFullName()
    {
        return strtoupper($this->fencer_surname) . ", " . $this->fencer_firstname;
    }

    public function save(array $options = [])
    {
        if (parent::save($options)) {
            Accreditation::makeDirty($this, null);
        }
    }
}
