<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\WPUser;
use App\Models\Event;
use App\Models\Schemas\ReturnStatus;
use Auth;

class Login extends Controller
{
    /**
     * Login the current session
     *
     * Attempt to validate the current session based on provided username and password combination
     *
     * @OA\Post(
     *     path = "/auth/login",
     *     @OA\RequestBody(ref="#/components/requestBodies/login"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful login",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     ),
     *     @OA\Response(
     *         response  = "403",
     *         description = "Unsuccessful login",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        if ($request->has('username') && $request->has('password')) {
            $username = validate_trim($request->post('username'));
            $password = validate_trim($request->post('password'));

            // flush all data, prevent session mixing between apps
            $request->session()->flush();
            if (Auth::attempt(['user_email' => $username, 'password' => $password])) {
                return response()->json(new ReturnStatus('ok'));
            }
        }

        return response()->json(new ReturnStatus('error', 'Invalid credentials'));
    }
}
