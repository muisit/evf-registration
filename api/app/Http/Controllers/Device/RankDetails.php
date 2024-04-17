<?php

namespace App\Http\Controllers\Device;

use App\Models\Fencer;
use App\Models\Weapon;
use App\Support\Services\RankDetailsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RankDetails extends Controller
{
    /**
     * View ranking details for a specific fencer
     *
     * @OA\Get(
     *     path = "/device/rankdetails/{weapon}/{uuid}",
     *     @OA\RequestBody(ref="#/components/requestBodies/follow"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Data returned successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RankDetails")
     *     ),
     *     @OA\Response(
     *         response  = "404",
     *         description = "Fencer not found",
     *     )
     * )
     */
    public function index(Request $request, string $weapon, string $uuid)
    {
        $fencer = Fencer::where('uuid', $uuid)->first();
        if (empty($fencer)) {
            return response('Fencer not found', 404);
        }
        $weaponObject = Weapon::where('weapon_abbr', strtoupper($weapon))->first();
        if (empty($weaponObject)) {
            return response('Weapon not found', 404);
        }
        $service = new RankDetailsService($fencer, $weapon);
        $result = $service->generate();
        if (empty($result)) {
            return response('Results not found', 404);
        }
        return response()->json($result);
    }
}
