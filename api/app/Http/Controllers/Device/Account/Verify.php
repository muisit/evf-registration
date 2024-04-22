<?php

namespace App\Http\Controllers\Device\Account;

use App\Http\Controllers\Device\Base;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

class Verify extends Base
{
    /**
     * (re)send verification e-mail
     *
     * @OA\Post(
     *     path = "/device/account/verify",
     *     @OA\Response(
     *         response = "200",
     *         description = "Account information",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $email = $this->only(['email']);
        $device = $request->get('device');
        if (empty($device) || $device->user->getKey() != $request->user()->getKey()) {
            return response()->json(new ReturnStatus('error', 'Invalid request'), 403);
        }
        if (!empty($device->verification_code_sent)) {
            $date = new Carbon($device->verification_code_sent);
            $diff = time() - $date->timestamp;
            if ($diff < 179) {
                return response()->json(new ReturnStatus('error', 'Invalid request'), 403);
            }
        }
        if (empty($email) || !isset($email->email)) {
            return response()->json(new ReturnStatus('error', 'Invalid request'), 403);
        }
        \Log::debug("email is " . json_encode($email));
        $device->email = $email->email;
        $device->verification_code = $this->getRandomCode();
        $device->verification_code_sent = Carbon::now()->toDateTimeString();
        $device->save();

        $notification = new VerifyEmail($device->email, $device->verification_code);
        Notification::route('mail', $device->email)->notify($notification);

        return response()->json(new ReturnStatus('ok'));
    }

    private function getRandomCode()
    {
        $nonzero = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $withzero = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $c1 = $nonzero[array_rand($nonzero)];
        $c2 = $withzero[array_rand($withzero)];
        $c3 = $withzero[array_rand($withzero)];
        $c4 = $withzero[array_rand($withzero)];
        $c5 = $withzero[array_rand($withzero)];
        $c6 = $withzero[array_rand($withzero)];
        return "$c1$c2$c3$c4$c5$c6";
    }
}
