<?php

namespace App\Models\Schemas;

use App\Models\Category as BaseModel;

/**
 * Category model
 *
 * @OA\Schema()
 */
class Category
{
    /**
     * ID of the category
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $id = null;

    /**
     * Name of the category
     *
     * @var string
     * @OA\Property()
     */
    public ?string $name = null;

    /**
     * Abbreviation of the category
     *
     * @var $string
     * @OA\Property()
     */
    public ?string $abbr = null;

    /**
     * Category type
     *
     * @var string
     * @OA\Property()
     */
    public ?string $type = null;

    /**
     * Category value
     *
     * @var int
     * @OA\Property()
     */
    public ?int $value = null;

    public function __construct(?BaseModel $data = null)
    {
        if (!empty($data)) {
            $this->id = $data->getKey();
            $this->name = $data->category_name;
            $this->abbr = $data->category_abbr;
            $this->type = $data->category_type;
            $this->value = $data->category_value;
        }
    }

    public static function categoryFromYear($year, $wrt)
    {
        $year = intval($year);
        $wrtM = intval(date('m', strtotime($wrt)));
        $wrtY = intval(date('Y', strtotime($wrt)));

        $diff = $wrtY - $year;
        if ($wrtM > 6) {
            $diff += 1; // people start fencing in the older category as of July
        }
        //if ($diff >= 80) return 5;
        if ($diff >= 70) return 4;
        if ($diff >= 60) return 3;
        if ($diff >= 50) return 2;
        if ($diff >= 40) return 1;
        return -1;
    }
}
