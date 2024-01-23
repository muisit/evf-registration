<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use App\Models\Requests\EventRole as EventRoleRequest;

class SaveRoles extends Controller
{
    /**
     * Save event data to the database
     *
     * @OA\Post(
     *     path = "/events/sides",
     *     @OA\RequestBody(ref="#/components/requestBodies/eventroles"),
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
        $form = new EventRoleRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new ReturnStatus('ok'));
        }
        return response()->json(new ReturnStatus('error', 'validation failure'));
    }
}
