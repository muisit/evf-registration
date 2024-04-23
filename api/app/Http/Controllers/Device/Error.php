<?php

namespace App\Http\Controllers\Device;

use App\Models\Audit;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use Illuminate\Support\Facades\Auth;

class Error extends Base
{
    /**
     * Accept a device end error situation
     *
     * @OA\Get(
     *     path = "/device/error",
     *     @OA\Response(
     *         response = "200",
     *         description = "Device status information",
     *         @OA\JsonContent(type="String")
     *     )
     * )
     */
    public function index(Request $request)
    {
        \Log::debug("storing new error message");
        $audit = new Audit();
        $audit->model_id = null;
        $audit->model_type = '';
        $audit->action = 'DeviceError';
        $audit->payload = ['message' => request()->getContent(), 'get' => $_GET, 'headers' => json_encode($request->header())];
        $audit->created_by = null;
        $audit->created_by_type = 'Error';
        $audit->save();
        return response()->json(new ReturnStatus('ok'));
    }
}
