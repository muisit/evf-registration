<?php

namespace App\Http\Controllers\FE\Ranking;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ranking;
use App\Models\Weapon;
use App\Models\Requests\FERequest;
use App\Models\Schemas\FE\Response;
use App\Models\Schemas\FE\Rank;
use App\Jobs\CreateRanking;
use App\Support\Services\RankingService;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Retrieve the current ranking
     *
     * @OA\Post(
     *     path = "/ranking/list",
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
        if ($request->get('path') != '/ranking/list') {
            \Log::debug('invalid path');
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            \Log::debug('empty model');
            $this->authorize('not/ever');
        }

        $cat = Category::find($model->category_id);
        $wpn = Weapon::find($model->weapon_id);
        if (empty($cat) || empty($wpn)) {
            \Log::debug('empty cat/wpn');
            $this->authorize('not/ever');
        }
        $ranking = RankingService::latest($cat, $wpn);
        if (empty($ranking)) {
            \Log::debug('empty ranking');
            $this->authorize('not/ever');
        }

        $positions = $ranking->positions()
            ->joinRelationshipUsingAlias('fencer', 'fencer')
            ->with('fencer')
            ->with('fencer.country')
            ->orderBy('position', 'asc')
            ->orderBy('fencer.fencer_surname', 'asc')
            ->orderBy('fencer.fencer_firstname', 'asc')
            ->orderBy('fencer.fencer_id', 'asc')
            ->get()
            ->map(fn ($i) => new Rank($i));

        return response()->json(new Response('ok', null, ['results' => $positions]));
    }
}
