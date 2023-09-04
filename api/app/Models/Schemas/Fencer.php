<?php

namespace App\Models\Schemas;

use App\Models\Fencer as BaseModel;

/**
 * Registration model
 *
 * @OA\Schema()
 */
class Fencer
{
    /**
     * Unique fencer id
     *
     * @var int
     * @OA\Property()
     */
    public ?int $id = null;

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
     * Country identifiers
     *
     * @var int
     * @OA\Property()
     */
    public ?int $countryId = null;

    /**
     * Gender
     *
     * @var string
     * @OA\Property()
     */
    public ?string $gender = null;

    /**
     * Date of birth
     *
     * @var string
     * @OA\Property()
     */
    public ?string $dateOfBirth = null;

    /**
     * Status of the photo ID
     *
     * @var string
     * @OA\Property()
     */
    public ?string $photoStatus = null;

    public function __construct(BaseModel $fencer)
    {
        $this->id = $fencer->getKey();
        $this->firstName = $fencer->fencer_firstname;
        $this->lastName = $fencer->fencer_surname;
        $this->countryId = intval($fencer->fencer_country) > 0 ? $fencer->fencer_country : null;
        $this->gender = $fencer->fencer_gender;
        $this->dateOfBirth = $fencer->fencer_dob;
        $this->photoStatus = $fencer->fencer_picture;
    }
}
