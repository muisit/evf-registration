<?php

namespace App\Models\Schemas;

use App\Models\Result as Model;

class Result
{
    /**
     * Event title
     * @var string
     * @OA\Property()
     */
    public string $event;

    /**
     * Event date
     * @var string
     * @OA\Property()
     */
    public string $date;

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

    public function __construct(?Model $result)
    {
        if (!empty($result)) {
            $this->entries = $result->result_entry;
            $this->position = $result->result_place;
            $this->points = $result->result_points;
            $this->de = $result->result_de_points;
            $this->podium = $result->result_podium_points;
            $this->total = $result->result_total_points;
            $this->status = $result->result_in_ranking == 'Y' ? 'Y' : 'N';
        }
    }
}
