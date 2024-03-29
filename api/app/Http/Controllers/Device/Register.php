<?php

namespace App\Http\Controllers\Device;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DeviceUser;
use App\Models\Schemas\Device as DeviceSchema;

class Register extends Base
{
    /**
     * Register a new device
     *
     * @OA\Get(
     *     path = "/device/register",
     *     @OA\Response(
     *         response = "200",
     *         description = "Device information",
     *         @OA\JsonContent(ref="#/components/schemas/Device")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = new DeviceUser();
        $user->save();

        $device = new Device();
        $device->device_user_id = $user->getKey();
        \Log::debug("received " . json_encode($request->getContent()));
        $doc = $this->only(['platform', 'version', 'language', 'vendor', 'manufacturer', 'model', 'osVersion', 'build', 'uid', 'data']);
        $device->platform = (array) $doc;
        $device->save();
        return response()->json(new DeviceSchema($device));
    }
}
