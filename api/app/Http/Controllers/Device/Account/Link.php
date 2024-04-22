<?php

namespace App\Http\Controllers\Device\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use App\Models\Requests\AccountLink;

class Link extends Controller
{
    /**
     * Link account to a specific fencer
     *
     * @OA\Post(
     *     path = "/device/account/link",
     *     @OA\RequestBody(ref="#/components/requestBodies/fencer"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
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
        $form = new AccountLink($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            if (!$model->exists && !$form->forceCreate) {
                return response()->json(new ReturnStatus('create'));
            }
            else {
                return response()->json(new ReturnStatus('ok'));
            }
        }
        return response()->json(new ReturnStatus('error', 'internal error'), 422);
    }
}
