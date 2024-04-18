<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Competition as BaseModel;

/**
 * Competition model for mobile devices
 *
 * @OA\Schema()
 */
class CompetitionDevice
{
    /**
     * ID of the competition
     *
     * @var integer
     * @OA\Property()
     */
    public int $id;

    /**
     * Category
     *
     * @var string
     * @OA\Property()
     */
    public string $category;

    /**
     * Weapon
     *
     * @var string
     * @OA\Property()
     */
    public string $weapon;

    /**
     * Start date for this competition
     *
     * @var $string
     * @OA\Property()
     */
    public string $starts;

    /**
     * Results linked to this competition
     *
     * @var ResultDevice[]
     * @OA\Property()
     */
    public ?array $results;

    public function __construct(BaseModel $model, bool $withResults = false)
    {
        $this->id = $model->getKey();
        $this->category = $model->category->category_abbr;
        $this->weapon = $model->weapon->weapon_abbr;
        $this->starts = $model->competition_opens;

        if ($withResults) {
            $this->results = [];
            foreach ($model->results()->with(['fencer', 'fencer.country'])->get() as $result) {
                $this->results[] = new ResultInCompetition($result);
            }
        }
    }
}
