<?php

namespace App\Models\Schemas;

class Result
{
    /**
     * Event title
     * @var string
     * @OA\Property()
     */
    public string $event;

    /**
     * Event year
     * @var int
     * @OA\Property()
     */
    public int $year;

    /**
     * Event location
     * @var string
     * @OA\Property()
     */
    public string $location;

    /**
     * Event country
     * @var string
     * @OA\Property()
     */
    public string $country;

    /**
     * Competition category
     * @var string
     * @OA\Property()
     */
    public string $category;
    
    /**
     * Competition weapon
     * @var string
     * @OA\Property()
     */
    public string $weapon;

    /**
     * Number of entries
     * @var int
     * @OA\Property()
     */
    public int $entries;

    /**
     * Result position
     * @var int
     * @OA\Property()
     */
    public int $position;

    /**
     * Points for position
     * @var double
     * @OA\Property()
     */
    public float $points;

    /**
     * DE points
     * @var double
     * @OA\Property()
     */
    public float $de;

    /**
     * Podium points
     * @var double
     * @OA\Property()
     */
    public float $podium;

    /**
     * Factor
     * @var double
     * @OA\Property()
     */
    public float $factor;

    /**
     * Total points
     * @var double
     * @OA\Property()
     */
    public float $total;


    /**
     * Status
     * @var string
     * @OA\Property()
     */
    public string $status;
}
