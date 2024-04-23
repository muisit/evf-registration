<?php

namespace App\Http\Controllers\Device;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use App\Models\Requests\Block as BlockRequest;

class Block extends Controller
{
    /**
     * Block or unblock a follower
     *
     * @OA\Post(
     *     path = "/device/block",
     *     @OA\RequestBody(ref="#/components/requestBodies/block"),
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
        $form = new BlockRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json(new ReturnStatus('error', 'internal error'), 422);
    }
}
