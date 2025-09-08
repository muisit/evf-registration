<?php

namespace App\Models;

use Carbon\Carbon;

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
        $wrt = Carbon::createFromFormat('Y-m-d', $wrt);
        if ($wrt !== false) {
            $wrtM = intval($wrt->format('m'));
            $wrtY = intval($wrt->format('Y'));

            $diff = $wrtY - $year;
            if ($wrtM > 6) {
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

    public function getMaximalDate($wrt = null)
    {
        $catval = intval($this->category_value);
        if (empty($wrt)) {
            $wrt = Carbon::now();
        }
        $year = intval($wrt->format('Y'));
        $month = intval($wrt->format('m'));
        // category switch is start of july
        if ($month > 6) $year = intval($year) + 1;

        switch ($catval) {
            default:
            case 1:
                $year -= 39;
                break;
            case 2:
                $year -= 49;
                break;
            case 3:
                $year -= 59;
                break;
            case 4:
                $year -= 69;
                break;
            case 5:
                $year -= 79;
                break;
        }
        return new Carbon('' . $year . '-01-01');
    }

    public function getMinimalDate($wrt = null)
    {
        $catval = intval($this->category_value);
        if (empty($wrt)) {
            $wrt = Carbon::now();
        }
        $year = intval($wrt->format('Y'));
        $month = intval($wrt->format('m'));
        // category switch is start of july
        if ($month > 6) $year = intval($year) + 1;

        switch ($catval) {
            default:
            case 1:
                $year -= 50;
                break;
            case 2:
                $year -= 60;
                break;
            case 3:
                $year -= 70;
                break;
            case 4:
                $year -= 199;
                break; // no max for cat 4 since we stopped cat 5
            case 5:
                $year -= 199;
                break;
        }
        return new Carbon('' . $year . '-01-01');
    }
}
