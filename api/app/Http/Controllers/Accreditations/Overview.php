<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Schemas\AccreditationsOverview as OverviewSchema;
use Auth;

class Overview extends Controller
{
    /**
     * Event accreditation overview
     *
     * @OA\Get(
     *     path = "/accreditations/overview",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of accessible accreditations",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AccreditationOverview")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $event = $request->get('eventObject');
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            abort(404);
        }
        // this is only available to users with the Accreditation role
        $this->authorize("accredit", $event);

        $retval = [];
        $lines = $event->accreditationOverview();
        foreach ($lines as $line) {
            $retval[] = new OverviewSchema($line[0], $line[1], $line[2], $line[3]);
        }
        return response()->json($retval);
    }
}
