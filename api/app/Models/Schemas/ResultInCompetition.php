<?php

namespace App\Models\Schemas;

use App\Models\Result;

class ResultInCompetition
{
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

    /**
     * Fencer
     * @var Fencer
     * @OA\Property()
     */
    public FencerPublic $fencer;

    public function __construct(?Result $result)
    {
        if (!empty($result)) {
            $this->fencer = new FencerPublic($result->fencer);
            $this->entries = $result->result_entry ?? 0;
            $this->position = $result->result_place ?? 0;
            $this->points = $result->result_points ?? 0.0;
            $this->de = $result->result_de_points ?? 0.0;
            $this->podium = $result->result_podium_points ?? 0.0;
            $this->total = $result->result_total_points ?? 0.0;
            $this->status = $result->result_in_ranking == 'Y' ? 'Y' : 'N';
        }
    }
}
