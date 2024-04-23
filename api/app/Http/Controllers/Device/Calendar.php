<?php

namespace App\Http\Controllers\Device;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Services\CalendarService;

class Calendar extends Controller
{
    /**
     * Get a list of calendar events
     *
     * @OA\Get(
     *     path = "/device/calendar",
     *     @OA\Response(
     *         response = "200",
     *         description = "Data returned successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Calendar")
     *     ),
     * )
     */
    public function index(Request $request)
    {
        return response()->json((new CalendarService())->generate());
    }
}
