<?php

namespace App\Http\Controllers\Device;

use App\Models\Competition;
use App\Models\Schemas\CompetitionDevice as CompetitionSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Results extends Controller
{
    /**
     * Get a list of results for a specific competition
     *
     * @OA\Get(
     *     path = "/device/results/{competitionId}",
     *     @OA\Response(
     *         response = "200",
     *         description = "Data returned successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CompetitionDevice")
     *     ),
     * )
     */
    public function index(Request $request, string $competitionId)
    {
        $competition = Competition::find($competitionId);
        if (empty($competition)) {
            return response('Not found', 404);
        }

        return response()->json(new CompetitionSchema($competition, true));
    }
}
