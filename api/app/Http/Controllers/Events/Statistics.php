<?php

namespace App\Http\Controllers\Events;

use App\Models\SideEvent;
use App\Support\Services\StatisticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Statistics extends Controller
{
    /**
     * Export some statistics on the event and the system
     *
     * @OA\Get(
     *     path = "/events/statistics",
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful login",
     *         @OA\JsonContent(ref="#/components/schemas/EventStatistics")
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $event = $request->get('eventObject');
        $this->authorize('organise', $event);
        return response()->json((new StatisticsService($event))->generate());
    }
}
