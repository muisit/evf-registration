<?php

namespace App\Models\Schemas;

use App\Models\Registration as BaseModel;

/**
 * Registration model
 *
 * @OA\Schema()
 */
class Payment
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
    public ?array $registrations = null;

    /**
     * HoD payment status
     *
     * @var string
     * @OA\Property()
     */
    public ?string $paidHod = null;

    /**
     * Organisation payment status
     *
     * @var string
     * @OA\Property()
     */
    public ?string $paidOrg = null;
}
