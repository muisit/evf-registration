<?php

namespace App\Http\Controllers\Device;

use Illuminate\Http\Request;
use App\Models\Schemas\Device as DeviceSchema;
use App\Models\Schemas\ReturnStatus;
use Illuminate\Support\Facades\Auth;

class RegisterStatus extends Base
{
    /**
     * Store updated device status information: messaging token, locale
     *
     * @OA\Post(
     *     path = "/device/register/status",
     *     @OA\Response(
     *         response = "200",
     *         description = "Device information",
     *         @OA\JsonContent(ref="#/components/schemas/Device")
     *     )
     * )
     */
    public function index(Request $request)
    {
        // get the actual device we are communicating with, not the user account
        $device = $request->get('device');
        if (!empty($device)) {
            $language = $request->get('locale');
            $messagingToken = $request->get('token');
            $device->platform = array_merge(
                $device->platform,
                [
                    'language' => $language,
                    'messagingToken' => $messagingToken
                ]
            );
            \Log::debug("saving language $language, messagingToken $messagingToken");
            $device->save();
            return response()->json(new DeviceSchema($device));
        }
        return response()->json(new ReturnStatus("error", "no such device registered"), 401);
    }
}
