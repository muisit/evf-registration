<?php

namespace App\Http\Controllers\Registrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Schemas\ReturnStatus;
use App\Models\Requests\RegistrationDelete;
use Auth;
use Carbon\Carbon;

class Delete extends Controller
{
    /**
     * Delete registration data from the database
     *
     * @OA\Post(
     *     path = "/registrations",
     *     @OA\RequestBody(@OA\Property(property="id", type="number")),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful removal",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     *     @OA\Response(
     *         response  = "422",
     *         description = "Unsuccessful removal",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $form = new RegistrationDelete($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json([], 403);
    }
}
