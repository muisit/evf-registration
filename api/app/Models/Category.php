<?php

namespace App\Models;

class Category extends Model
{
    protected $table = 'TD_Category';
    protected $primaryKey = 'category_id';
    public $timestamps = false;

    public const CAT1 = 1;
    public const CAT2 = 2;
    public const CAT3 = 3;
    public const CAT4 = 4;
    public const CAT5 = 7;
    public const TEAM = 5;
    public const GVET = 6;
}
