<?php

namespace App\Http\Controllers\Device\Account;

use App\Http\Controllers\Device\Base;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use App\Support\Services\AccountStatusService;

class Get extends Base
{
    /**
     * Retrieve account details
     *
     * @OA\Get(
     *     path = "/device/account",
     *     @OA\Response(
     *         response = "200",
     *         description = "Account information",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceAccount")
     *     )
     * )
     */
    public function index(Request $request)
    {
        \Log::debug("returning account details");
        $user = $request->user();
        $device = $request->get('device');
        if (empty($user) || empty($device) || $device->device_user_id != $user->getKey()) {
            \Log::debug("user " . json_encode($user) . " device " . json_encode($device));
            return response()->json(new ReturnStatus('error', 'Invalid request'), 403);
        }
        $service = new AccountStatusService();
        return response()->json($service->generate($user, $device));
    }
}
