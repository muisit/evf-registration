<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Schemas\EventSimple;
use App\Models\Schemas\ReturnStatus;
use Carbon\Carbon;

class Get extends Controller
{
    /**
     * Single event
     *
     * @OA\Get(
     *     path = "/events/{id}",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of accessible events",
     *         @OA\JsonContent(ref="#/components/schemas/EventSimple")
     *     )
     *     @OA\Response(
     *         response = "404",
     *         description = "Event not found",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request, int $eventId)
    {
        $now = Carbon::now()->subDays(14);
        $event = Event::where('event_id', $eventId)->where('event_open', '>', $now->toDateTimeString())->first();
        if (!empty($event) && $event->useAccreditationApplication()) {
            return response()->json(new EventSimple($event));
        }
        return response()->json(new ReturnStatus('error', 'No such event'), 404);
    }
}
