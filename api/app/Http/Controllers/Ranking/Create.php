<?php

namespace App\Http\Controllers\Ranking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Services\RankingStoreService;
use App\Models\Schemas\ReturnStatus;
use Carbon\Carbon;

class Create extends Controller
{
    /**
     * Generate the ranking based on the current data
     *
     * @OA\Get(
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
        $service = new RankingStoreService();
        $service->handle();
        return response()->json(new ReturnStatus('ok'));
    }
}
