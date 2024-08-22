<?php

namespace App\Http\Controllers\Results;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competition;
use App\Models\Schemas\ReturnStatus;
use App\Jobs\ResultFeedEvents;

class Trigge extends Controller
{
    /**
     * Trigger events around storing new results. We need this to connect the Wordpress
     * backend and the Laravel application event handling
     *
     * @OA\Get(
     *     path = "/results/trigger",
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
        $competitionId = $request->get('competition_id');
        $competition = Competition::where('competition_id', $competitionId)->first();
        if (!empty($competition)) {
            dispatch(new ResultFeedEvents($competition));
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json(new ReturnStatus('error', 'no such competition'), 404);
    }
}
