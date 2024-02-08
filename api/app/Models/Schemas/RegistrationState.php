<?php

namespace App\Models\Schemas;

use App\Models\Registration as BaseModel;

/**
 * Registration model
 *
 * @OA\Schema()
 */
class RegistrationState
{
    /**
     * Contained registrations
     *
     * @var int[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="int")
     * )
     */
    public array $registrations = [];

    /**
     * New status value
     *
     * @var string
     * @OA\Property()
     */
    public string $value = '';

    /**
     * Expected previous value. Null if dont-care
     *
     * @var string
     * @OA\Property()
     */
    public ?string $previous = null;
}
