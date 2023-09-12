<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Schemas\Fencer as FencerSchema;
use App\Support\Services\DuplicateFencerService;
use Auth;
use Carbon\Carbon;

class Duplicate extends Controller
{
    /**
     * Check for matching entries in the database
     *
     * @OA\Post(
     *     path = "/fencers/autocomplete",
     *     @OA\RequestBody(ref="#/components/requestBodies/duplicate"),
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful check",
     *     ),
     *     @OA\Response(
     *         response = "401",
     *         description = "Duplicate found",
     *         @OA\JsonContent(ref="#/components/schemas/Fencer")
     *     ),
     * )
     */
    public function index(Request $request)
    {
        // the duplication check is in essence on the entire database, so no need to check
        // if the current user has specific privileges.
        // However, the current user _must_ have view rights on the country of the fencer
        // passed to the check.
        $fencer = $request->get('fencer');
        $countryId = $fencer["countryId"] ?? -1;
        $country = Country::where('country_id', $countryId)->first();

        // country is a required setting for the fencer. It must be a viewable country and if
        // there is a country set, it must match that of the fencer
        if (
               empty($country)
            || !$request->user()->can('view', $country)
            || ($request->has('countryObject') && $request->get('countryObject')->getKey() != $country->getKey())
        ) {
            $this->authorize('not/ever');
        }

        $service = new DuplicateFencerService();
        $duplicateCheck = $service->check($fencer);
        
        if (!empty($duplicateCheck)) {
            return response()->json(new FencerSchema($duplicateCheck), 406);
        }
        else {
            return response()->json(null);
        }
    }
}
