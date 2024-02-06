<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Schemas\ReturnStatus;
use Auth;
use Carbon\Carbon;

class Generate extends Controller
{
    /**
     * Regenerate the special accreditation codes for functional access
     *
     * @OA\Get(
     *     path = "/events/generate",
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
    public function index(Request $request)
    {
        $event = $request->get('eventObject');
        $this->authorize('update', $event);
        $event->generateFunctionalCodes();
        return response()->json(new ReturnStatus('ok'));
    }
}
