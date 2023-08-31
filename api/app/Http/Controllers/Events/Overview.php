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
    public function index(Request $request)
    {
        $event = $request->get('eventObject');
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            $request->user()->authorize("not/ever");
        }
        $request->user()->authorize("view", $event);

        $lines = $event->overview($user->hasRole(['sysop','organisation:' . $event->getKey(), 'superhod']));
        foreach ($lines as $line) {
            $retval[] = new OverviewSchema($line);
        }
        return response()->json($retval);
    }
}
