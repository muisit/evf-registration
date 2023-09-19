<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fencer;
use Auth;
use Carbon\Carbon;

class Photo extends Controller
{
    /**
     * Return a picture associated with the indicated fencer
     *
     * @OA\Get(
     *     path = "/fencers/{fencerId}/photo",
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\Schema(@OA\Property(type="string", format="binary"))
     *     ),
     *     @OA\Response(
     *         response  = "403",
     *         description = "Access not allowed",
     *     )
     * )
     */
    public function index(Request $request, string $fencerId)
    {
        $fencer = Fencer::where('fencer_id', $fencerId)->first();
        if (empty($fencer)) {
            $this->authorize('not/ever');
        }
        $this->authorize('view', $fencer);

        $imageLocation = $fencer->image();
        if (!empty($imageLocation) && file_exists($imageLocation) && is_readable($imageLocation)) {
            header('Content-Disposition: inline;');
            header('Content-Type: image/jpeg');
            header('Expires: ' . (time() + 2 * 24 * 60 * 60));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($imageLocation));
            readfile($imageLocation);
        }
        else {
            $this->authorize('not/ever');
        }
    }
}
