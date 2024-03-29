<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WPPostMeta extends Model
{
    public $timestamps = false;
    protected $fillable = [];
    protected $table = 'postmeta';
    protected $primaryKey = 'meta_id';

    public function __construct(array $attributes = [])
    {
        $this->table = env('WPDBPREFIX', 'wp_') . $this->table;
        parent::__construct($attributes);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(WPPost::class, 'post_id', 'ID');
    }
}
