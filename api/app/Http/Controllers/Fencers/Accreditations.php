<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fencer;
use App\Models\Accreditation;
use App\Models\Schemas\Accreditation as AccreditationSchema;
use App\Support\Services\AutocompleteService;
use Auth;
use Carbon\Carbon;

class Accreditations extends Controller
{
    /**
     * List fencer accreditations
     *
     * @OA\Get(
     *     path = "/fencers/{fencer}/accreditations",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of accessible accreditations",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Accreditation")
     *         )
     *     )
     * )
     */
    public function index(Request $request, string $fencerId)
    {
        \Log::debug("looking for fencer $fencerId");
        $fencer = Fencer::where('fencer_id', $fencerId)->first();
        if (empty($fencer)) {
            $this->authorize('not/ever');
        }
        \Log::debug("authorizing view fencer");
        $this->authorize('view', $fencer);
        \Log::debug("authorizing view-any accreditation");
        $this->authorize('viewAny', Accreditation::class);

        $retval = [];
        foreach ($fencer->accreditations as $accreditation) {
            if ($request->user()->can('view', $accreditation)) {
                $retval[] = new AccreditationSchema($accreditation);
            }
        }

        return response()->json($retval);
    }
}
