<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Schemas\Event as EventSchema;
use Auth;
use Carbon\Carbon;

class Index extends Controller
{
    /**
     * List of events
     *
     * @OA\Get(
     *     path = "/events",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of accessible events",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Event")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $now = Carbon::now()->subDays(14);
        $events = Event::where('event_open', '>', $now->toDateTimeString())->get();
        $retval = [];
        foreach ($events as $event) {
            if ($request->user()->can('view', $event)) {
                $retval[] = new EventSchema($event);
            }
        }
        return response()->json($retval);
    }
}
