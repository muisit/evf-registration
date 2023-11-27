<?php

namespace App\Http\Controllers\Registrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Schemas\ReturnStatus;
use App\Models\Requests\RegistrationPay;
use Auth;
use Carbon\Carbon;

class Pay extends Controller
{
    /**
     * Mark payment of registration data in the database
     *
     * @OA\Post(
     *     path = "/registrations/pay",
     *     @OA\RequestBody(ref="#/components/requestBodies/payment"),
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
        $form = new RegistrationPay($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json([], 403);
    }
}
