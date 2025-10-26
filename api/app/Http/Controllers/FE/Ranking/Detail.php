<?php

namespace App\Http\Controllers\FE\Ranking;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ranking;
use App\Models\Weapon;
use App\Models\Fencer;
use App\Models\Schemas\FE\Response;
use App\Models\Schemas\FE\RankDetail;
use App\Support\Services\RankingService;
use Illuminate\Http\Request;

class Detail extends Controller
{
    /**
     * Retrieve the ranking position detail
     *
     * @OA\Post(
     *     path = "/ranking/detail",
     *     @OA\Response(
     *         response = "200",
     *         description = "Ranking",
     *         @OA\JsonContent(ref="#/components/schemas/Ranking")
     *     )
     *     @OA\Response(
     *         response = "404",
     *         description = "No data found",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        if ($request->get('path') != '/ranking/detail') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $cat = Category::find($model->category_id);
        $wpn = Weapon::find($model->weapon_id);
        $fencer = Fencer::where('uuid', $model->id)->first();
        if (empty($cat) || empty($wpn) || empty($fencer)) {
            $this->authorize('not/ever');
        }

        $results = RankingService::details($fencer, $cat, $wpn);
        if (empty($results)) {
            $this->authorize('not/ever');
        }
        $schemas = $results->map(fn ($r) => new RankDetail($r, $cat, $wpn));
        return response()->json(new Response('ok', null, $schemas));
    }
}
