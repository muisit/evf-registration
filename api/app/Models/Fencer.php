<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kirschbaum\PowerJoins\PowerJoins;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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

    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    public static function rules()
    {
        return [
            'fencer_id' => ['required', 'int', 'min:0'],
            'fencer_firstname' => ['required','max:45','min:2'],
            'fencer_surname' => ['required','max:45','min:2'],
            'fencer_country' => ['required','exists:TD_Country,country_id'],
            'fencer_gender' => ['required', Rule::in(['M', 'F'])],
            'fencer_dob' => ['nullable', 'date_format:Y-m-d', 'before:' . Carbon::now()->subMinutes(1)->toDateString()],
            'fencer_picture' => ['nullable', Rule::in(['N','Y','A','R'])]
        ];
    }

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

    public function labels(): HasMany
    {
        return $this->hasMany(FencerLabel::class, 'fencer_id', 'fencer_id');
    }
    public function addLabel($label, $type)
    {
        $fl = new FencerLabel();
        $fl->label = $label;
        $fl->type = in_array($type, ['first', 'last']) ? $type : 'first';
        $fl->fencer_id = $this->getKey();
        $fl->save();
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

    public function getCountryOfRegistration(Event $event)
    {
        $country = Registration::where('registration_fencer', $this->getKey())
            ->where('registration_mainevent', $event->getKey())
            ->where('registration_country', '<>', null)
            ->pluck('registration_country')
            ->first();
        return empty($country) ? $this->fencer_country : $country;
    }
}
