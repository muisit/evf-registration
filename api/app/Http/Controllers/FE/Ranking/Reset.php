<?php

namespace App\Http\Controllers\FE\Ranking;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Schemas\FE\Response;
use App\Jobs\CreateRanking;
use App\Support\Services\AssessResultsService;
use Illuminate\Http\Request;

class Reset extends Controller
{
    /**
     * Recalculate all the currently included results that make up the ranking
     *
     * @OA\Post(
     *     path = "/ranking/create",
     *     @OA\Response(
     *         response = "200",
     *         description = "Ranking generated",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
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
        $this->authorize('viewAny', Result::class);
        $service = new AssessResultsService();
        $total = $service->handle();

        dispatch(new CreateRanking());

        return response()->json(new Response('ok', null, ['total' => $total]));
    }
}
