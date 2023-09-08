<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\SideEvent as BaseModel;

/**
 * SideEvent model
 *
 * @OA\Schema()
 */
class SideEvent
{
    /**
     * ID of the side-event
     *
     * @var integer
     * @OA\Property()
     */
    public int $id;

    /**
     * Title of the side-event
     * 
     * @var string
     * @OA\Property()
     */
    public string $title;

    /**
     * Description of this side-event
     * 
     * @var $string
     * @OA\Property()
     */
    public string $description;

    /**
     * Start date for this side-event
     * 
     * @var $string
     * @OA\Property()
     */
    public string $starts;

    /**
     * Costs in event currencies for participation
     * 
     * @var float
     * @OA\Property()
     */
    public float $costs;

    /**
     * Related competition
     * 
     * @var int
     * @OA\Property()
     */
    public ?int $competitionId;

    public function __construct(BaseModel $model)
    {
        $this->id = $model->getKey();
        $this->title = $model->title;
        $this->description = $model->description;
        $this->starts = $model->starts;
        $this->costs = floatval($model->costs);
        $this->competitionId = empty($model->competition_id) ? null : $model->competition_id;
    }
}
