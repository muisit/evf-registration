<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Requests\AccreditationDocument as DocumentRequest;
use App\Models\Schemas\AccreditationDocument as DocumentSchema;
use Auth;

class SaveDocument extends Controller
{
    /**
     * Save the state of the Accreditation Document
     *
     * @OA\Post(
     *     path = "/accreditations/document",
     *     @OA\RequestBody(ref="#/components/requestBodies/accreditationdocument"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\JsonContent(ref="#/components/schemas/AccreditationDocument")
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
        $form = new DocumentRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new DocumentSchema($model));
        }
        return response()->json([]);
    }
}
