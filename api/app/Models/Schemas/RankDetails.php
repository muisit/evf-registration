<?php

namespace App\Models\Schemas;

class RankDetails
{
    /**
     * Fencer ID
     *
     * @var string
     * @OA\Property()
     *
     */
    public string $fencer;

    /**
     * Weapon
     * @var string
     * @OA\Property()
     */
    public string $weapon;

    /**
     * Category
     * @var string
     * @OA\Property()
     */
    public string $category;

    /**
     * Date of the ranking
     * @var string
     * @OA\Property()
     */
    public string $date;

    /**
     * Position on the ranking
     * @var int
     * @OA\Property()
     */
    public int $position;

    /**
     * Total points
     * @var double
     * @OA\Property()
     */
    public float $points;

    /**
     * Results
     *
     * @var Result[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Result")
     * )
     */
    public ?array $results = null;
}
