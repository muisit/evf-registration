<?php

namespace App\Http\Controllers\Device;

use Illuminate\Http\Request;
use App\Support\Services\DeviceStatusService;
use Illuminate\Support\Facades\Auth;

class Status extends Base
{
    /**
     * Retrieve the device status on feeds, calendar, etc
     *
     * @OA\Get(
     *     path = "/device/status",
     *     @OA\Response(
     *         response = "200",
     *         description = "Device status information",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $retval = (new DeviceStatusService())->handle();
        return response()->json($retval);
    }
}
