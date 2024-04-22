<?php

namespace App\Models\Schemas;

use App\Models\Fencer as BaseModel;
use App\Models\Event;

/**
 * Registration model
 *
 * @OA\Schema()
 */
class FencerPrivate
{
    /**
     * Unique fencer uuid
     *
     * @var string
     * @OA\Property()
     */
    public ?string $id = null;

    /**
     * First name
     *
     * @var string
     * @OA\Property()
     */
    public ?string $firstName = null;

    /**
     * Last name
     *
     * @var string
     * @OA\Property()
     */
    public ?string $lastName = null;

    /**
     * Date of Birth
     *
     * @var string
     * @OA\Property()
     */
    public ?string $dateOfBirth = null;

    /**
     * Gender
     *
     * @var string
     * @OA\Property()
     */
    public ?string $gender = null;

    /**
     * Country abbreviation
     *
     * @var int
     * @OA\Property()
     */
    public ?string $country = null;

    /**
     * Picture status
     *
     * @var int
     * @OA\Property()
     */
    public ?string $picture = null;


    public function __construct(BaseModel $fencer)
    {
        $this->id = $fencer->uuid;
        $this->firstName = $fencer->fencer_firstname;
        $this->lastName = strtoupper($fencer->fencer_surname);
        $this->dateOfBirth = $fencer->fencer_dob;
        $this->gender = $fencer->fencer_gender == 'F' ? 'F' : 'M';
        $this->country = $fencer->country?->country_abbr ?? 'OTH';

        switch ($fencer->fencer_picture ?? 'N') {
            default:
            case 'N':
                $this->picture = 'N';
                break;
            case 'Y':
            case 'R':
            case 'A':
                $this->picture = $fencer->fencer_picture;
                break;
        }
    }
}
