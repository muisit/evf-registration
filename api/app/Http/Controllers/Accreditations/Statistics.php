<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Services\AccreditationStatisticsService;
use Auth;

class Statistics extends Controller
{
    /**
     * List of statistics concerning event registrations and accreditation status
     *
     * @OA\Get(
     *     path = "/accreditations/statistics",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of statistics per event",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AccreditationStatistics")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $retval = [];
        $event = $request->get('eventObject');
        \Log::debug("checking dt on event");
        if (!empty($event) && $request->user()->can('dt', $event)) {
            return (new AccreditationStatisticsService($event))->generate();
        }
        return response()->json($retval);
    }
}
