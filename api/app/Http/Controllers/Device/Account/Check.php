<?php

namespace App\Http\Controllers\Device\Account;

use App\Models\Audit;
use App\Http\Controllers\Device\Base;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use Carbon\Carbon;

class Check extends Base
{
    /**
     * Check the entered code against the pending device email code
     *
     * @OA\Post(
     *     path = "/device/account/check",
     *     @OA\Response(
     *         response = "200",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $code = $this->only(['code']);
        $device = $request->get('device');
        if (empty($device) || $device->user->getKey() != $request->user()->getKey()) {
            \Log::debug("device empty, or user does not match request user");
            return response()->json(new ReturnStatus('error', 'Invalid request'), 403);
        }
        if (empty($device->verification_code_sent)) {
            \Log::debug("verification code was never sent");
            return response()->json(new ReturnStatus('error', 'Invalid request'), 403);
        }
        if (empty($code) || !isset($code->code)) {
            \Log::debug("code is missing or empty");
            return response()->json(new ReturnStatus('error', 'Invalid request'), 403);
        }

        if ($device->verification_code != $code->code) {
            \Log::debug($device->verification_code . " != " . $code->code);
            return response()->json(new ReturnStatus('code', 'Invalid code'), 200);
        }
        else {
            \Log::debug("upgrading device email");
            if ($this->updateEmail($device)) {
                return response()->json(new ReturnStatus('merged'));
            }
            else {
                return response()->json(new ReturnStatus('ok'));
            }
        }
    }

    private function updateEmail($device)
    {
        // clear the device settings
        $device->verification_code_sent = null;
        $device->verification_code = null;
        $device->save();

        $user = DeviceUser::where('email', $device->email)->first();

        if (empty($user)) {
            Audit::create($device, "linkEmail", $device->user->email, $device->email);
            // upgrade the e-mail to the user e-mail
            $user = $device->user;
            $user->email = $device->email;
            $user->email_verified_at = Carbon::now()->toDateTimeString();
            $user->save();
            return false; // no merge
        }
        else {
            // We have another device user with the same e-mail address.
            // There is no other alternative than to merge these accounts
            Audit::create($device, "mergeEmail", $device->user, $user);
            $user->mergeWith($device->user);
            return true;
        }
    }
}
