<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Kirschbaum\PowerJoins\PowerJoins;

class WPPost extends Model
{
    use PowerJoins;

    public $timestamps = false;
    protected $fillable = [];
    protected $table = 'posts';
    protected $primaryKey = 'ID';

    public function __construct(array $attributes = [])
    {
        $this->table = env('WPDBPREFIX', 'wp_') . $this->table;
        parent::__construct($attributes);
    }

    public function meta(): HasMany
    {
        return $this->hasMany(WPPostMeta::class, 'post_id', 'ID');
    }

    public function scopeIsEvent(Builder $query): void
    {
        $query->where('post_type', 'tribe_events');
    }

    public function scopeIsVenue(Builder $query): void
    {
        $query->where('post_type', 'tribe_venue');
    }

    public function scopeIsOrganizer(Builder $query): void
    {
        $query->where('post_type', 'tribe_organizer');
    }

    public function scopeIsPost(Builder $query): void
    {
        $query->where('post_type', 'post');
    }
}
