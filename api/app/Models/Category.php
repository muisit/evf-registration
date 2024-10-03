<?php

namespace App\Models;

use DateTimeImmutable;

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

    public static function categoryFromYear($year, $wrt)
    {
        $year = intval($year);
        $wrt = DateTimeImmutable::createFromFormat('Y-m-d', $wrt);
        if ($wrt !== false) {
            $wrtM = intval($wrt->format('m'));
            $wrtY = intval($wrt->format('Y'));

            $diff = $wrtY - $year;
            if($wrtM > 6) {
                $diff += 1; // people start fencing in the older category as of July
            }
            //if ($diff >= 80) return 5;
            if ($diff >= 70) return 4;
            if ($diff >= 60) return 3;
            if ($diff >= 50) return 2;
            if ($diff >= 40) return 1;
        }
        return -1;
    }
}
