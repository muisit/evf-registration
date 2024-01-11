<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Country;
use App\Models\Schemas\AccreditationTemplate as Schema;
use App\Models\Requests\AccreditationTemplate as Request;
use Auth;
use Carbon\Carbon;

class Save extends Controller
{
    /**
     * Save template data to the database
     *
     * @OA\Post(
     *     path = "/templates",
     *     @OA\RequestBody(ref="#/components/requestBodies/accreditationtemplate"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\JsonContent(ref="#/components/schemas/AccreditationTemplate")
     *     ),
     *     @OA\Response(
     *         response  = "422",
     *         description = "Unsuccessful save",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationStatus")
     *     )
     * )
     */
    public function index(HttpRequest $request)
    {
        $form = new Request($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new Schema($model));
        }
        return response()->json([]);
    }
}
