<?php

namespace App\Models\Schemas;

use App\Models\RankingPosition as PositionModel;

/**
 * Ranking Position model
 *
 * @OA\Schema()
 */
class RankingPosition
{
    /**
     * uuid of the fencer
     *
     * @var string
     * @OA\Property()
     */
    public string $id = '';

    /**
     * Last name of the fencer, upper case
     *
     * @var string
     * @OA\Property()
     */
    public string $name = '';

    /**
     * First name of the fencer
     *
     * @var string
     * @OA\Property()
     */
    public string $firstName = '';

    /**
     * Country abbreviation of the fencer
     *
     * @var string
     * @OA\Property()
     */
    public string $country = '';

    /**
     * Position
     *
     * @var int
     * @OA\Property()
     */
    public int $pos = 0;

    /**
     * Points awarded
     *
     * @var float
     * @OA\Property()
     */
    public float $points = 0.0;

    public function __construct(PositionModel $model)
    {
        $this->id = $model->fencer->uuid;
        $this->name = strtoupper($model->fencer->fencer_surname ?? '');
        $this->firstName = $model->fencer->fencer_firstname;
        $this->country = $model->fencer->country->country_abbr;
        $this->pos = $model->position;
        $this->points = $model->points;
    }
}
