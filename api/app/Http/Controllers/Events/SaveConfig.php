<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schemas\EventSimple as EventSchema;
use App\Models\Requests\EventConfig as EventRequest;

class SaveConfig extends Controller
{
    /**
     * Save event configuration data to the database
     *
     * @OA\Post(
     *     path = "/events/config",
     *     @OA\RequestBody(ref="#/components/requestBodies/eventconfig"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\JsonContent(ref="#/components/schemas/EventSimple")
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
