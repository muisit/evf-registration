<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;

/**
 * Overview row model
 *
 * @OA\Schema()
 */
class Overview
{
    /**
     * ID of the country
     *
     * @var integer
     * @OA\Property()
     */
    public string $country;

    /**
     * List of side-event-id => counts
     * @var object[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(
     *       type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="count", type="array", @OA\Items(type="integer")),
     *   )
     * )
     */
    public array $counts;

    public function __construct(string $id, array $counts)
    {
        $this->country = $id;
        $this->counts = $counts;
    }
}
