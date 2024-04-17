<?php

namespace App\Models\Schemas;

use App\Models\Fencer as BaseModel;
use App\Models\Event;

/**
 * Registration model
 *
 * @OA\Schema()
 */
class FencerPublic
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
     * Country identifiers
     *
     * @var int
     * @OA\Property()
     */
    public ?string $country = null;

    public function __construct(BaseModel $fencer)
    {
        $this->id = $fencer->uuid;
        $this->firstName = $fencer->fencer_firstname;
        $this->lastName = strtoupper($fencer->fencer_surname);
        $this->country = $fencer->country?->country_name ?? 'Other';
    }
}
