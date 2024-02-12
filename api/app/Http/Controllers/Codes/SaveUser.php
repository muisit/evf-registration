<?php

namespace App\Http\Controllers\Codes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Requests\AccreditationUser as UserRequest;
use App\Models\Schemas\AccreditationUser as UserSchema;
use Auth;

class SaveUser extends Controller
{
    /**
     * Update the state of an AccreditationUser
     *
     * @OA\Post(
     *     path = "/codes/users",
     *     @OA\RequestBody(ref="#/components/requestBodies/accreditationuser"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\JsonContent(ref="#/components/schemas/AccreditationUser")
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
        $form = new UserRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            \Log::debug("model is " . json_encode($model));
            return response()->json(new UserSchema($model));
        }
        return response()->json([]);
    }
}
