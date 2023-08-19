<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Country as BaseModel;

/**
 * Country model
 *
 * @OA\Schema()
 */
class Country
{
    /**
     * ID of the country
     *
     * @var integer
     * @OA\Property()
     */
    public int $id;

    /**
     * Name of the country
     *
     * @var string
     * @OA\Property()
     */
    public string $name;

    /**
     * Abbreviation of the country
     *
     * @var $string
     * @OA\Property()
     */
    public string $abbr;

    /**
     * Path to the flag image
     *
     * @var string
     * @OA\Property()
     */
    public ?string $path;


    public function __construct(?BaseModel $country = null)
    {
        if (!empty($country)) {
            $this->id = $country->getKey();
            $this->name = $country->country_name;
            $this->abbr = $country->country_abbr;
            $this->path = $country->country_flag_path;
        }
    }
}
