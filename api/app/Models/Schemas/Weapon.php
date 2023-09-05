<?php

namespace App\Models\Schemas;

use App\Models\Weapon as BaseModel;

/**
 * Weapon model
 *
 * @OA\Schema()
 */
class Weapon
{
    /**
     * ID of the weapon
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $id = null;

    /**
     * Name of the weapon
     *
     * @var string
     * @OA\Property()
     */
    public ?string $name = null;

    /**
     * Abbreviation of the weapon
     *
     * @var $string
     * @OA\Property()
     */
    public ?string $abbr = null;

    /**
     * Weapon gender
     *
     * @var string
     * @OA\Property()
     */
    public ?string $gender = null;

    public function __construct(?BaseModel $data = null)
    {
        if (!empty($data)) {
            $this->id = $data->getKey();
            $this->name = $data->weapon_name;
            $this->abbr = $data->weapon_abbr;
            $this->gender = $data->weapon_gender;
        }
    }
}
