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
     * @OA\Post(
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
        $device = $request->get('deviceObject');
        if (!empty($device)) {
            $language = $request->get('language');
            $messagingToken = $request->get('messagingToken');
            $device->platform = array_merge(
                $device->platform,
                [
                    'language' => $language,
                    'messagingToken' => $messagingToken
                ]
            );
            $device->save();
        }

        $retval = (new DeviceStatusService())->handle();
        return response()->json($retval);
    }
}
