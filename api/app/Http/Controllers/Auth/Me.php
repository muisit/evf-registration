<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\WPUser;
use App\Schemas\Me as MeSchema;
use Auth;

class Me extends Controller
{
    /**
     * Return information about the current session
     *
     * Return user and session information, required to initialise the CSRF token
     * and establish user rights
     *
     * @OA\Get(
     *     path = "/auth/me",
     *     @OA\Parameter(
     *         in = "query",
     *         name = "event",
     *         description = "Event identifier to get authorization data on",
     *         required = false,
     *         style = "form",
     *         explode = "false",
     *         @OA\Schema(
     *             type = "integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful retrieval",
     *         @OA\JsonContent(ref="#/components/schemas/Me")
     *     )
     * )
     */
    public function index(Request $request)
    {
        if (Auth::guest()) {
            return response()->json(new MeSchema());
        }
        else {
            return response()->json(new MeSchema(Auth::user(), $request->get('eventObject')));
        }
    }
}
