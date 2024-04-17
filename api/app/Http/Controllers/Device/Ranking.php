<?php

namespace App\Http\Controllers\Device;

use App\Models\Category;
use App\Models\Weapon;
use App\Models\Ranking as RankingModel;
use App\Models\Schemas\Ranking as RankingSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Ranking extends Controller
{
    /**
     * View a specific ranking
     *
     * @OA\Get(
     *     path = "/device/ranking/{weapon}/{category}",
     *     @OA\RequestBody(ref="#/components/requestBodies/follow"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Data returned successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Ranking")
     *     ),
     *     @OA\Response(
     *         response  = "404",
     *         description = "Ranking not found",
     *     )
     * )
     */
    public function index(Request $request, string $weapon, string $category)
    {
        $date = $request->get('last');
        $weapon = Weapon::where('weapon_abbr', $weapon)->first();
        $category = Category::where('category_abbr', $category)->first();

        if (empty($weapon) || empty($category)) {
            return response('Not found', 404);
        }

        $query = RankingModel::where('weapon_id', $weapon->getKey())
            ->where('category_id', $category->getKey())
            ->orderBy('ranking_date', 'desc');
        if (!empty($date)) {
            $query->where('ranking_date', '<=', $date);
        }
        $ranking = $query->first();

        if (empty($ranking)) {
            return response('Not found', 404);
        }

        $schema = new RankingSchema($ranking);
        return response()->json($schema);
    }
}
