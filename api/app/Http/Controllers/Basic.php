<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Services\BasicDataService;

class Basic extends Controller
{
    /**
     * List of basic data
     *
     * @OA\Get(
     *     path = "/basic",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of basic data",
     *         @OA\JsonContent(
    *             ref="#/components/schemas/BasicData"
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return response()->json((new BasicDataService())->create($request->get('restrict') ?? ''));
    }
}
