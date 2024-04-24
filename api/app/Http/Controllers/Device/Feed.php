<?php

namespace App\Http\Controllers\Device;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Services\FeedService;

class Feed extends Controller
{
    /**
     * Get a list of feed items
     *
     * @OA\Get(
     *     path = "/device/feed",
     *     @OA\Response(
     *         response = "200",
     *         description = "Data returned successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Feed")
     *     ),
     * )
     */
    public function index(Request $request)
    {
        return response()->json((new FeedService())->generate($request->user()));
    }
}
