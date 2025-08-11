<?php

namespace App\Http\Controllers\FE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schemas\FE\Response;
use App\Jobs\CreateRanking as Job;

class CreateRanking extends Controller
{
    /**
     * Generate the ranking based on the current data
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
        \Log::debug("creating ranking store service");
        $job = new Job();
        $job->handle();
        \Log::debug("returning all ok");
        return response()->json(new Response('ok'));
    }
}
