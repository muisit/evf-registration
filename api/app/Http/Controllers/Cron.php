<?php

namespace App\Http\Controllers;

use App\Models\WPUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schemas\ReturnStatus;
use App\Jobs\CheckDirtyBadges;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Cron extends Controller
{
    /**
     * Schedule jobs
     *
     * @OA\Get(
     *     path = "/cron",
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful execution",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $content = date('YmdHis') . '1';
        $token = Crypt::encryptString($content);
        $reContent = Crypt::decryptString($token);
        \Log::debug("$content -> $token -> $reContent");

        try {
            $token = $request->get('token');
            \Log::debug("token is " . $token);
            $decrypted = Crypt::decryptString($token);
            \Log::debug("decrypted is $decrypted / " . substr($decrypted, 14));

            $user = WPUser::where('ID', substr($decrypted, 14))->first();
            if ($user->can('sysop', WPUser::class)) {
                if ($request->has('dirty')) {
                    \Log::debug("dispatching CheckDirtyBadges");
                    dispatch(new CheckDirtyBadges());
                }
                return response()->json(new ReturnStatus('ok'));
            }
            else {
                \Log::debug("user cannot sysop");
            }
        } catch (DecryptException $e) {
        }
        \Log::debug("die 403");
        return response()->json(new ReturnStatus('error', 'Unauthorised'), 403);
    }
}
