<?php

namespace App\Http\Controllers\Ranking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ranking;
use App\Models\Category;
use App\Models\Weapon;
use App\Models\Schemas\Ranking as RankingSchema;
use App\Models\Schemas\ReturnStatus;
use Carbon\Carbon;

class Get extends Controller
{
    /**
     * Single ranking
     *
     * https://.../ranking/ws/1
     *
     * @OA\Get(
     *     path = "/ranking/{weapon}/{category}",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of accessible events",
     *         @OA\JsonContent(ref="#/components/schemas/Ranking")
     *     )
     *     @OA\Response(
     *         response = "404",
     *         description = "Event not found",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request, string $weapon, string $category)
    {
        $category = Category::where('category_abbr', $category)->first();
        $weapon = Weapon::where('weapon_abbr', strtoupper($weapon))->first();
        if (empty($weapon) || empty($category) || $category->category_type != 'I' || !in_array(intval($category->category_value), [1,2,3,4])) {
            return response()->json(new ReturnStatus('error', 'No such ranking'), 404);
        }

        $ranking = Ranking::orderBy('ranking_date', 'desc')->first();
        if (empty($ranking)) {
            return response()->json(new ReturnStatus('error', 'No such ranking'), 404);
        }
        return response()->json(new RankingSchema($ranking, $category, $weapon));
    }
}
