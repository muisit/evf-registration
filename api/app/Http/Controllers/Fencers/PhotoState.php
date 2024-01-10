<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Schemas\ReturnStatus;
use App\Models\Requests\FencerPhoto as FencerPhotoRequest;
use Auth;
use Carbon\Carbon;

class PhotoState extends Controller
{
    /**
     * Save fencer data to the database
     *
     * @OA\Post(
     *     path = "/fencers/photostate",
     *     @OA\RequestBody(ref="#/components/requestBodies/fencerphoto"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     *     @OA\Response(
     *         response  = "422",
     *         description = "Unsuccessful save",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $form = new FencerPhotoRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json(new ReturnStatus('error', 'Not authorized'), 403);
    }
}
