<?php

namespace App\Http\Controllers\FE\Fencers;

use App\Http\Controllers\Controller;
use App\Models\Fencer;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Schemas\FE\Fencer as FEFencer;
use App\Support\Services\PhotoAssessService;
use Illuminate\Http\Request;

class Upload extends Controller
{
    public function index(Request $request)
    {
        $fid = intval($request->get('fencer'));
        $fencer = Fencer::find($fid);
        if (empty($fencer)) {
            return response()->json(["success" => false], 403);
        }
        if (empty($request->file('picture'))) {
            $this->authorize('not/ever');
        }
        $this->authorize('update', $fencer);

        if ($request->hasFile('picture')) {
            $imageLocation = $fencer->image();
            $mimeType = $request->file('picture')->getMimeType();
            $request->file('picture')->move(dirname($imageLocation), basename($imageLocation));

            $filename = PhotoAssessService::convert($imageLocation, $mimeType);
            if (!empty($filename)) {
                $fencer->fencer_picture = 'Y';
                $fencer->save();
                // only return the fencer_picture attribute
                return response()->json(new WPResponse(["model" => ["picture" => $fencer->fencer_picture]]));
            }
            else {
                $fencer->fencer_picture = 'N';
                $fencer->save();
                if (file_exists($imageLocation)) {
                    @unlink($imageLocation);
                }
                return response()->json(["success" => false]);
            }
        }
    }
}
