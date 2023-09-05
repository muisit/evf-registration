<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\WPUser;
use App\Models\Event;
use App\Models\Schemas\ReturnStatus;
use Auth;

class Logout extends Controller
{
    /**
     * Logout and destroy the current session
     *
     *
     * @OA\Post(
     *     path = "/auth/logout",
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful logout",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     *     @OA\Response(
     *         response  = "403",
     *         description = "Unsuccessful logout",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        // not logged in, always okay
        if (empty(Auth::user())) {
            return response()->json(new ReturnStatus('ok'));
        }

        Auth::logout();
        return response()->json(new ReturnStatus('ok'));
    }
}
