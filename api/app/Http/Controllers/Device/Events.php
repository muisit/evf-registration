<?php

namespace App\Http\Controllers\Device;

use App\Models\Event;
use App\Models\Schemas\EventDevice as EventSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Events extends Controller
{
    /**
     * Get a list of events with results
     *
     * @OA\Get(
     *     path = "/device/events",
     *     @OA\Response(
     *         response = "200",
     *         description = "Data returned successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $events = Event::has('competitions.results')->with(['competitions', 'country', 'competitions.category', 'competitions.weapon'])->get();
        if (empty($events)) {
            return response('Not found', 404);
        }

        $retval = $events->map(fn ($e) => new EventSchema($e))->toArray();
        return response()->json($retval);
    }
}
