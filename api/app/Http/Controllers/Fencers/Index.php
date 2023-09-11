<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fencer;
use App\Models\Schemas\Fencer as FencerSchema;
use App\Support\Services\AutocompleteService;
use Auth;
use Carbon\Carbon;

class Index extends Controller
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

        if ((!$allowedWithCountry && !$allowedAll) || !$request->has('countryObject')) {
            $this->authorize('not/ever');
        }

        $fencers = Fencer::where('fencer_country', $request->get('countryObject')->getKey())
            ->orderBy('fencer_surname', 'asc')->orderBy('fencer_firstname', 'asc')->orderBy('fencer_id', 'asc')->get();
        $retval = [];
        if (!emptyResult($fencers)) {
            foreach ($fencers as $fencer) {
                if ($request->user()->can('view', $fencer)) {
                    $retval[] = new FencerSchema($fencer);
                }
            }
        }

        return response()->json($retval);
    }
}
