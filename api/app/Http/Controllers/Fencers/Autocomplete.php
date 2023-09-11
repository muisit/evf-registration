<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fencer;
use App\Models\Schemas\Fencer as FencerSchema;
use App\Support\Services\AutocompleteService;
use Auth;
use Carbon\Carbon;

class Autocomplete extends Controller
{
    /**
     * List fencers matching the search term(s)
     *
     * @OA\Get(
     *     path = "/fencers/autocomplete",
     *     @OA\Parameter(
     *         in = "query",
     *         name = "country",
     *         description = "Country to restrict search to",
     *         required = false,
     *         style = "form",
     *         explode = "false",
     *         @OA\Schema(
     *             type = "integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         in = "query",
     *         name = "name",
     *         description = "Name starting value to search with",
     *         required = true,
     *         style = "form",
     *         explode = "false",
     *         @OA\Schema(
     *             type = "string"
     *         )
     *     ),
     *     @OA\Response(
     *         response = "200",
     *         description = "List of accessible fencers",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Fencer")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $allowedWithCountry = $request->has('countryObject') && $request->user()->can('view', $request->get('countryObject'));
        $allowedAll = $request->user()->can('viewAny', Fencer::class);

        \Log::debug(json_encode([$allowedWithCountry, $allowedAll, $request->has('name'), $request->get('name')]));
        if ((!$allowedWithCountry && !$allowedAll) || !$request->has('name')) {
            \Log::debug('not evaaa');
            $this->authorize('not/ever');
        }

        $fencers = (new AutocompleteService())->search($request->get('name'), $request->get('countryObject'));
        $retval = [];
        foreach ($fencers as $fencer) {
            if ($request->user()->can('view', $fencer)) {
                $retval[] = new FencerSchema($fencer);
            }
        }

        \Log::debug("returning list of " . count($retval) . ' fencers');
        return response()->json($retval);
    }
}
