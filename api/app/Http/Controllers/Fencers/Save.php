<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Schemas\Fencer as FencerSchema;
use App\Models\Requests\Fencer as FencerRequest;
use Auth;
use Carbon\Carbon;

class Save extends Controller
{
    /**
     * Check for matching entries in the database
     *
     * @OA\Post(
     *     path = "/fencers",
     *     @OA\RequestBody(ref="#/components/requestBodies/fencer"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\JsonContent(ref="#/components/schemas/Fencer")
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
        $form = new FencerRequest($this);
        $model = $form->validate($request);
        return response()->json(new FencerSchema($model));
    }
}
