<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Schemas\Event as EventSchema;
use App\Models\Requests\Event as EventRequest;
use Auth;
use Carbon\Carbon;

class Save extends Controller
{
    /**
     * Save event data to the database
     *
     * @OA\Post(
     *     path = "/events",
     *     @OA\RequestBody(ref="#/components/requestBodies/event"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response  = "422",
     *         description = "Unsuccessful save",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $form = new EventRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new EventSchema($model));
        }
        return response()->json([]);
    }
}
