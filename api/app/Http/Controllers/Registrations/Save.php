<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Requests\Fencer as FencerRequest;
use App\Models\Schemas\ReturnStatus;
use Auth;
use Carbon\Carbon;

class Save extends Controller
{
    /**
     * Save fencer data to the database
     *
     * @OA\Post(
     *     path = "/fencers",
     *     @OA\RequestBody(ref="#/components/requestBodies/registration"),
     *   @OA\Response(
     *       response = "200",
     *       description = "Successful store",
     *       @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *   ),
     * )
     */
    public function index(Request $request)
    {
        $form = new FencerRequest($this);
        $model = $form->validate($request);
        return response()->json(new FencerSchema($model));
    }
}
