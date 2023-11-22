<?php

namespace App\Http\Controllers\Registrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Requests\Registration as RegistrationRequest;
use App\Models\Schemas\Registration;
use Auth;
use Carbon\Carbon;

class Save extends Controller
{
    /**
     * Save fencer data to the database
     *
     * @OA\Post(
     *     path = "/registrations",
     *     @OA\RequestBody(ref="#/components/requestBodies/registration"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful store",
     *         @OA\JsonContent(ref="#/components/schemas/Registration")
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
        $form = new RegistrationRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new Registration($model));
        }
        return response()->json([], 403);
    }
}
