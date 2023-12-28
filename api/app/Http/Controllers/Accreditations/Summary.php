<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Requests\Summary as SummaryRequest;
use App\Models\Event;
use App\Jobs\SetupSummary;
use App\Models\Schemas\ReturnStatus;

class Summary extends Controller
{
    /**
     * Create a summary document
     *
     * @OA\Post(
     *     path = "/accreditations/summary",
     *     @OA\RequestBody(ref="#/components/requestBodies/summary"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Job was queued succesfully",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     *     @OA\Response(
     *         response  = "403",
     *         description = "Access denied",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $form = new SummaryRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json(new ReturnStatus('error', 'Unauthorized'), 403);
    }
}
