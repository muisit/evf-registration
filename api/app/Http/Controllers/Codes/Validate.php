<?php

namespace App\Http\Controllers\Codes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Schemas\CodeProcessStatus;
use App\Models\Requests\Codes;
use App\Support\Services\Codes\CodeService;

class Validate extends Controller
{
    /**
     * Validate a set of codes and perform actions
     *
     * @OA\Get(
     *     path = "/codes",
     *     @OA\Response(
     *         response = "200",
     *         description = "Code action status",
     *         @OA\JsonContent(ref="#/components/schemas/CodeProcessStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $form = new Codes($this);
        $event = $form->validate($request);
        if (!empty($event)) {
            $service = new CodeService($event);
            $result = $service->handle($form->action, $form->codes);
            if ($result !== false) {
                return response()->json($result);
            }
            return response()->json(new CodeProcessStatus(0, 'error', $result->action, implode(",", $result->errors)), 403);
        }
        return response()->json(new CodeProcessStatus(0, 'error', 'error', 'Invalid codes'), 403);
    }
}
