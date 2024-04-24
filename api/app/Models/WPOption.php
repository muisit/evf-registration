<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class WPOption extends Model
{
    public $timestamps = false;
    protected $fillable = [];
    protected $table = 'options';
    protected $primaryKey = 'option_id';

    public function __construct(array $attributes = [])
    {
        $this->table = env('WPDBPREFIX', 'wp_') . $this->table;
        parent::__construct($attributes);
    }
}
