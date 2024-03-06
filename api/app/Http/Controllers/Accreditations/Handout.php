<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Requests\Handout as HandoutRequest;
use App\Models\Schemas\ReturnStatus;
use Auth;

class Handout extends Controller
{
    /**
     * Acknowledge handing out a badge
     *
     * @OA\Post(
     *     path = "/accreditations/handout",
     *     @OA\RequestBody(ref="#/components/requestBodies/accreditationdocument"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful login",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     *     @OA\Response(
     *         response  = "422",
     *         description = "Unsuccessful save",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationStatus")
     *     )
     * )
     * )
     */
    public function index(Request $request)
    {
        \Log::debug("controller for handout signal");
        $form = new HandoutRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus("ok"));
        }
        return response()->json([]);
    }
}
