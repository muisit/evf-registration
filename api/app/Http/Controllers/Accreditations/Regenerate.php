<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Jobs\RegenerateBadges;
use App\Models\Schemas\ReturnStatus;

class Regenerate extends Controller
{
    /**
     * Regenerate event accreditations
     *
     * @OA\Get(
     *     path = "/accreditations/regenerate",
     *     @OA\Response(
     *         response = "200",
     *         description = "Job was queued succesfully",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     *     @OA\Response(
     *         response  = "403",
     *         description = "Access denied",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
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

        dispatch(new RegenerateBadges($event));
        return response()->json(new ReturnStatus('ok'));
    }
}
