<?php

namespace App\Models\Schemas;

use App\Models\Category;
use App\Models\Ranking as RankingModel;
use App\Models\Weapon;

/**
 * Ranking model
 *
 * @OA\Schema()
 */
class Ranking
{
    /**
     * Date of the ranking
     *
     * @var string
     * @OA\Property()
     */
    public string $date = '';

    /**
     * Name of the category
     *
     * @var string
     * @OA\Property()
     */
    public string $category = '';

    /**
     * Name of the weapon
     *
     * @var string
     * @OA\Property()
     */
    public string $weapon = '';

    /**
     * Ranking posititions
     *
     * @var string
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="RankingPosition")
     * )     */
    public array $positions = [];

    public function __construct(?RankingModel $ranking = null)
    {
        if (!empty($ranking)) {
            $this->date = (new \DateTimeImmutable($ranking->ranking_date))->format('Y-m-d');
            $this->category = $ranking->category->category_name;
            $this->weapon = $ranking->weapon->weapon_name;

            $positions = $ranking->positions()->with('fencer')->get();
            foreach ($positions as $position) {
                $this->positions[] = new RankingPosition($position);
            }
        }
    }
}
