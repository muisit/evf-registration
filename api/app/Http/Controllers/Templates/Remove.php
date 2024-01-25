<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccreditationTemplate;
use App\Models\Schemas\ReturnStatus;

class Remove extends Controller
{
    /**
     * Save template data to the database
     *
     * @OA\Post(
     *     path = "/templates/remove",
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
    public function index(Request $request)
    {
        $templateData = $request->get('template');
        if (!empty($templateData) && isset($templateData['id'])) {
            $template = AccreditationTemplate::where('id', $templateData['id'])->first();
        
            if (!empty($template)) {
                $this->authorize('delete', $template);
                $template->delete();
                return response()->json(new ReturnStatus('ok'));
            }
        }
        return response()->json(new ReturnStatus('error', 'no such template'), 404);
    }
}
