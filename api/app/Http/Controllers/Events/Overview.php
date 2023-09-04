<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Schemas\Overview as OverviewSchema;
use Auth;

class Overview extends Controller
{
    /**
     * Event registration overview
     *
     * @OA\Get(
     *     path = "/events",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of accessible events",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Overview")
     *         )
     *     )
     * )
     */
    public function index(Request $request, string $event)
    {
        $event = Event::where('event_id', $event)->first();
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            $this->authorize("not/ever");
        }

        $retval = [];
        if ($request->user()->can("view", $event)) {
            \Log::debug("user can see event");
            $isOrganiser = $request->user()->hasRole(['sysop','organisation:' . $event->getKey(), 'superhod']);
            $lines = $event->overview($isOrganiser);
            foreach ($lines as $key => $line) {
                $retval[] = new OverviewSchema($key, $line);
            }
            return response()->json($retval);
        }
        else {
            $this->authorize("not/ever");
        }
    }
}
