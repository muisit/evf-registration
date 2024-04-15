<?php

namespace App\Http\Controllers\Device;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use App\Models\Requests\Follow as FollowRequest;

class Follow extends Controller
{
    /**
     * Adjust follower configurations
     *
     * @OA\Post(
     *     path = "/device/follow",
     *     @OA\RequestBody(ref="#/components/requestBodies/follow"),
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
        $form = new FollowRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json(new ReturnStatus('error', 'internal error'), 422);
    }
}
