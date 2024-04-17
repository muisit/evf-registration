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
     * Last update date of the ranking
     *
     * @var string
     * @OA\Property()
     */
    public string $updated = '';

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
            $this->updated = (new \DateTimeImmutable($ranking->updated_at))->format('Y-m-d');
            $this->category = $ranking->category->category_abbr;
            $this->weapon = $ranking->weapon->weapon_abbr;

            $positions = $ranking->positions()->with(['fencer', 'fencer.country'])->get();
            foreach ($positions as $position) {
                $this->positions[] = new RankingPosition($position);
            }
        }
    }
}
