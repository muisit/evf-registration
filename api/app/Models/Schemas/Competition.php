<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Competition as BaseModel;

/**
 * Competition model
 *
 * @OA\Schema()
 */
class Competition
{
    /**
     * ID of the competition
     *
     * @var integer
     * @OA\Property()
     */
    public int $id;

    /**
     * Category ID
     * 
     * @var integer
     * @OA\Property()
     */
    public int $categoryId;

    /**
     * Weapon ID
     * 
     * @var integer
     * @OA\Property()
     */
    public int $weaponId;

    /**
     * Start date for this competition
     * 
     * @var $string
     * @OA\Property()
     */
    public string $starts;

    /**
     * Start date for weapons-check for this competition
     * 
     * @var $string
     * @OA\Property()
     */
    public string $weaponsCheck;

    public function __construct(BaseModel $model)
    {
        $this->id = $model->getKey();
        $this->categoryId = $model->competition_category;
        $this->weaponId = $model->competition_weapon;
        $this->starts = $model->competition_opens;
        $this->weaponsCheck = $model->competition_weapon_check;
    }
}
