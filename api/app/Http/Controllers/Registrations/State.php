<?php

namespace App\Http\Controllers\Registrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Schemas\ReturnStatus;
use App\Models\Requests\RegistrationState;
use Auth;
use Carbon\Carbon;

class State extends Controller
{
    /**
     * Mark the state of registration data in the database
     *
     * @OA\Post(
     *     path = "/registrations/state",
     *     @OA\RequestBody(ref="#/components/requestBodies/registrationstate"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successfully stored",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     *     @OA\Response(
     *         response  = "422",
     *         description = "Unsuccessfully stored",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $form = new RegistrationState($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json([], 403);
    }
}
